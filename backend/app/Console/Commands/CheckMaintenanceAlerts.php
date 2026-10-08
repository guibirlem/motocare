<?php

namespace App\Console\Commands;

use App\Models\Alerta;
use App\Services\FcmService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckMaintenanceAlerts extends Command
{
    protected $signature = 'alertas:verificar';

    protected $description = 'Envia alertas de óleo e revisão ao atingir 90% do limite de km';

    public function handle(FcmService $fcm): int
    {
        $failures = 0;
        $sent = 0;
        Alerta::where('status', 'pendente')
            ->whereIn('tipo_alerta', ['troca_oleo', 'revisao'])
            ->where('km_limite', '>', 0)
            ->whereHas('motocicleta', fn ($query) => $query->whereRaw('km_atual * 10 >= alertas.km_limite * 9'))
            ->chunkById(100, function ($alertas) use ($fcm, &$failures, &$sent) {
                foreach ($alertas as $alerta) {
                    $lock = Cache::lock('alerta.send.'.$alerta->id, 120);
                    if (! $lock->get()) {
                        continue;
                    }
                    try {
                        $alerta->refresh()->load('motocicleta.usuario');
                        $moto = $alerta->motocicleta;
                        if ($alerta->status !== 'pendente' || $moto->km_atual * 10 < $alerta->km_limite * 9) {
                            continue;
                        }
                        $token = $moto->usuario->fcm_token;
                        if (! $token) {
                            $this->warn("Alerta {$alerta->id}: usuário sem token FCM; permanece pendente.");

                            continue;
                        }
                        $fcm->send($alerta, $token);
                        $alerta->update([
                            'status' => 'notificado',
                            'nivel_urgencia' => $moto->km_atual >= $alerta->km_limite ? 'alta' : 'media',
                        ]);
                        $sent++;
                    } catch (Throwable $exception) {
                        $failures++;
                        Log::warning('Falha no envio do alerta de manutenção.', ['alerta_id' => $alerta->id, 'exception_type' => $exception::class]);
                        $this->error("Alerta {$alerta->id}: falha no envio; permanece pendente para nova tentativa.");
                    } finally {
                        $lock->release();
                    }
                }
            });
        $this->info("Alertas enviados: {$sent}. Falhas: {$failures}.");

        return $failures ? self::FAILURE : self::SUCCESS;
    }
}
