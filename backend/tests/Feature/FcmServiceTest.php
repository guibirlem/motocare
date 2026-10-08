<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Motocicleta;
use App\Services\FcmService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FcmServiceTest extends TestCase
{
    public function test_oauth_and_fcm_payload_and_http_failure(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);
        $path = tempnam(sys_get_temp_dir(), 'motocare-test-');
        file_put_contents($path, json_encode([
            'project_id' => 'test-project', 'client_email' => 'test@example.com', 'private_key' => $privateKey,
        ]));
        config(['firebase.credentials' => $path]);
        Http::preventStrayRequests();
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'oauth-token']),
            'fcm.googleapis.com/*' => Http::sequence()->push(['name' => 'projects/test-project/messages/1'])->push([], 503),
        ]);
        $alert = new Alerta(['id' => 7, 'tipo_alerta' => 'troca_oleo', 'km_limite' => 1000]);
        $alert->id = 7;
        $moto = new Motocicleta(['marca' => 'Honda', 'modelo' => 'CG', 'km_atual' => 900]);
        $moto->id = 3;
        $alert->setRelation('motocicleta', $moto);
        try {
            $service = new FcmService;
            $service->send($alert, 'device-token');
            Http::assertSent(fn ($request) => str_contains($request->url(), 'messages:send')
                && $request->hasHeader('Authorization', 'Bearer oauth-token')
                && $request['message']['token'] === 'device-token'
                && $request['message']['data']['alerta_id'] === '7');
            $this->expectException(RequestException::class);
            $service->send($alert, 'device-token');
        } finally {
            unlink($path);
        }
    }
}
