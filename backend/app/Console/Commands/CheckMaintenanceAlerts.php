<?php

namespace App\Console\Commands;

use App\Models\Alerta;
use App\Services\WebPushService;
use App\Support\MotorcycleServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckMaintenanceAlerts extends Command
{
    protected $signature = 'alertas:verificar';

    protected $description = 'Envia alertas ao atingir 90% do intervalo de km ou a data limite';

    public function handle(WebPushService $push): int
    {
        $failures = 0;
        $sent = 0;
        Alerta::where('status', 'pendente')
            ->whereIn('tipo_alerta', array_keys(MotorcycleServices::alerts()))
            ->where(function ($query) {
                $query->where(function ($kmQuery) {
                    $kmQuery->where('km_limite', '>', 0)->whereHas('motocicleta', fn ($motoQuery) => $motoQuery->whereRaw('km_atual * 10 >= alertas.km_limite * 9 + COALESCE(alertas.km_base, 0)'));
                })->orWhereDate('data_limite', '<=', today()->toDateString());
            })
            ->chunkById(100, function ($alertas) use ($push, &$failures, &$sent) {
                foreach ($alertas as $alerta) {
                    $lock = Cache::lock('alerta.send.'.$alerta->id, 120);
                    if (! $lock->get()) {
                        continue;
                    }
                    try {
                        $alerta->refresh()->load('motocicleta.usuario');
                        $moto = $alerta->motocicleta;
                        $kmDue = $alerta->km_limite > 0 && $moto->km_atual * 10 >= $alerta->km_limite * 9 + ($alerta->km_base ?? 0);
                        $dateDue = $alerta->data_limite && $alerta->data_limite->lte(today());
                        if ($alerta->status !== 'pendente' || (! $kmDue && ! $dateDue)) {
                            continue;
                        }
                        $subscription = $moto->usuario->push_subscription;
                        if (! $subscription) {
                            $this->warn("Alerta {$alerta->id}: navegador sem inscrição push; permanece pendente.");

                            continue;
                        }
                        $push->send($alerta, $subscription);
                        $alerta->update([
                            'status' => 'notificado',
                            'nivel_urgencia' => (($alerta->km_limite > 0 && $moto->km_atual >= $alerta->km_limite) || $dateDue) ? 'alta' : 'media',
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
