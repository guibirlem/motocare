<?php

namespace App\Services;

use App\Models\Alerta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FcmService
{
    public function send(Alerta $alerta, string $token): void
    {
        $path = config('firebase.credentials');
        if (! $path || ! is_readable($path)) {
            throw new RuntimeException('Configure FIREBASE_CREDENTIALS com o caminho do JSON da conta de serviço.');
        }
        $credentials = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        foreach (['project_id', 'client_email', 'private_key'] as $key) {
            if (empty($credentials[$key])) {
                throw new RuntimeException('Credenciais Firebase incompletas.');
            }
        }
        $cacheKey = 'fcm.oauth.'.hash('sha256', $credentials['client_email'].$credentials['private_key']);
        $accessToken = Cache::remember($cacheKey, 3000, function () use ($credentials) {
            $encode = fn (string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
            $now = time();
            $jwt = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])).'.'.$encode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));
            if (! openssl_sign($jwt, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('Não foi possível assinar a credencial Firebase.');
            }
            $response = Http::asForm()->connectTimeout(5)->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt.'.'.$encode($signature),
            ])->throw();
            $accessToken = $response->json('access_token');
            if (! is_string($accessToken) || $accessToken === '') {
                throw new RuntimeException('Firebase não retornou um token de acesso.');
            }

            return $accessToken;
        });

        $moto = $alerta->motocicleta;
        $service = $alerta->tipo_alerta === 'troca_oleo' ? 'Troca de óleo' : 'Revisão';
        Http::withToken($accessToken)->connectTimeout(5)->timeout(15)->post(
            'https://fcm.googleapis.com/v1/projects/'.rawurlencode($credentials['project_id']).'/messages:send',
            ['message' => [
                'token' => $token,
                'notification' => [
                    'title' => 'MotoCare: '.$service,
                    'body' => "{$moto->marca} {$moto->modelo}: {$moto->km_atual} km. Limite: {$alerta->km_limite} km.",
                ],
                'data' => ['alerta_id' => (string) $alerta->id, 'motocicleta_id' => (string) $moto->id],
                'webpush' => ['notification' => ['tag' => 'motocare-alerta-'.$alerta->id]],
            ]]
        )->throw();
    }
}
