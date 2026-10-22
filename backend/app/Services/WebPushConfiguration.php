<?php

namespace App\Services;

use Minishlink\WebPush\VAPID;
use RuntimeException;
use Throwable;

class WebPushConfiguration
{
    public function prepareOpenSsl(): void
    {
        if (PHP_OS_FAMILY === 'Windows' && ! getenv('OPENSSL_CONF')) {
            $path = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
            if (is_file($path)) {
                putenv('OPENSSL_CONF='.$path);
            }
        }
    }

    public function keys(): array
    {
        $this->prepareOpenSsl();
        $path = config('webpush.key_path');
        if (! is_string($path) || ! is_readable($path)) {
            throw new RuntimeException('Execute php artisan push:configurar para preparar as chaves locais.');
        }
        try {
            $keys = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            $auth = ['subject' => config('webpush.subject'), 'publicKey' => $keys['publicKey'] ?? '', 'privateKey' => $keys['privateKey'] ?? ''];
            VAPID::validate($auth);

            return $auth;
        } catch (Throwable) {
            throw new RuntimeException('Chaves Web Push locais inválidas. Preserve o arquivo e confira a configuração.');
        }
    }

    public function status(): array
    {
        try {
            $keys = $this->keys();

            return ['configured' => true, 'publicKey' => $keys['publicKey']];
        } catch (RuntimeException) {
            return ['configured' => false, 'publicKey' => null];
        }
    }

    public function initialize(): void
    {
        $this->prepareOpenSsl();
        $path = config('webpush.key_path');
        if (is_file($path)) {
            $this->keys();

            return;
        }
        if (! is_dir(dirname($path)) && ! mkdir(dirname($path), 0700, true) && ! is_dir(dirname($path))) {
            throw new RuntimeException('Não foi possível preparar o diretório de chaves.');
        }
        $keys = VAPID::createVapidKeys();
        $handle = fopen($path, 'x');
        if (! $handle) {
            $this->keys();

            return;
        }
        try {
            if (fwrite($handle, json_encode($keys, JSON_THROW_ON_ERROR)) === false) {
                throw new RuntimeException('Falha ao salvar as chaves.');
            }
        } finally {
            fclose($handle);
        }
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($path, 0600);
        }
        $this->keys();
    }
}
