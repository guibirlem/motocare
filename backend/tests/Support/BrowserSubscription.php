<?php

namespace Tests\Support;

use App\Services\WebPushConfiguration;

trait BrowserSubscription
{
    private function browserSubscription(string $suffix = 'browser'): array
    {
        app(WebPushConfiguration::class)->prepareOpenSsl();
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => 'prime256v1']);
        $details = openssl_pkey_get_details($key);
        $encode = fn (string $value) => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');

        // OpenSSL pode omitir zeros iniciais; cada coordenada P-256 precisa ter 32 bytes.
        $publicKey = "\x04".str_pad($details['ec']['x'], 32, "\x00", STR_PAD_LEFT).str_pad($details['ec']['y'], 32, "\x00", STR_PAD_LEFT);

        return ['endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/'.$suffix, 'keys' => ['p256dh' => $encode($publicKey), 'auth' => $encode(random_bytes(16))]];
    }
}
