<?php

use App\Services\WebPushConfiguration;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Verificacao de leitura, somente por CLI: nao migra, nao gera chaves nem envia push.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$projectRoot = dirname(__DIR__, 2);
if (! is_file($projectRoot.'/backend/vendor/autoload.php')) {
    fwrite(STDERR, "Dependencias do backend ausentes. Execute composer install no servidor.\n");
    exit(1);
}

require $projectRoot.'/backend/vendor/autoload.php';

try {
    $app = require $projectRoot.'/backend/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    $origin = rtrim((string) config('app.url'), '/');
    $host = parse_url($origin, PHP_URL_HOST);
    $checks = [
        'php_8_2_ou_superior' => PHP_VERSION_ID >= 80200,
        'ambiente_production' => app()->environment('production'),
        'debug_desligado' => config('app.debug') === false,
        'chave_app_configurada' => filled(config('app.key')),
        'origem_https_definida' => parse_url($origin, PHP_URL_SCHEME) === 'https'
            && is_string($host) && str_contains($host, '.')
            && ! str_ends_with($host, '.example.com') && ! str_ends_with($host, '.local'),
        'frontend_na_mesma_origem' => rtrim((string) config('password_recovery.frontend_url'), '/') === $origin,
        'sessao_no_banco' => config('session.driver') === 'database',
        'cookie_secure' => config('session.secure') === true,
        'cookie_httponly' => config('session.http_only') === true,
        'cookie_samesite_lax' => config('session.same_site') === 'lax',
        'build_frontend_presente' => is_readable($projectRoot.'/frontend/dist/index.html')
            && is_readable($projectRoot.'/frontend/dist/precache.json'),
        'webpush_subject_correto' => config('webpush.subject') === $origin,
        'storage_gravavel' => is_writable($projectRoot.'/backend/storage')
            && is_writable($projectRoot.'/backend/bootstrap/cache'),
    ];
    try {
        $connection = DB::connection();
        $database = $connection->selectOne('SELECT VERSION() AS version, @@default_storage_engine AS engine');
        $checks['mysql_8_0_innodb'] = $connection->getDriverName() === 'mysql'
            && str_starts_with((string) $database->version, '8.0.')
            && strcasecmp((string) $database->engine, 'InnoDB') === 0;
        $checks['banco_sem_usuario_root'] = config('database.connections.mysql.username') !== 'root';
    } catch (Throwable) {
        $checks['mysql_8_0_innodb'] = false;
        $checks['banco_sem_usuario_root'] = false;
    }
    $checks['vapid_existente_valido'] = $app->make(WebPushConfiguration::class)->status()['configured'];
    foreach ($checks as $label => $ok) {
        echo ($ok ? 'OK ' : 'PENDENTE ').$label.PHP_EOL;
    }
    echo 'SMTP e recebimento push exigem validacao separada; este comando nao envia mensagens.'.PHP_EOL;
    exit(in_array(false, $checks, true) ? 1 : 0);
} catch (Throwable) {
    fwrite(STDERR, "Verificacao incompleta. Confira a configuracao privada no servidor.\n");
    exit(1);
}
