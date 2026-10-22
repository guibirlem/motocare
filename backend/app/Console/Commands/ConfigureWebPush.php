<?php

namespace App\Console\Commands;

use App\Services\WebPushConfiguration;
use Illuminate\Console\Command;
use Throwable;

class ConfigureWebPush extends Command
{
    protected $signature = 'push:configurar';

    protected $description = 'Prepara e verifica as chaves locais de Web Push sem serviços externos';

    public function handle(WebPushConfiguration $configuration): int
    {
        try {
            $configuration->initialize();
            $this->info('Web Push preparado. Ative as notificações em Configurações no navegador.');

            return self::SUCCESS;
        } catch (Throwable) {
            $this->error('Não foi possível preparar o Web Push. Confira o caminho local e o OpenSSL. Chaves existentes não foram substituídas.');

            return self::FAILURE;
        }
    }
}
