<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Motocicleta;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MaintenanceAlertsTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\BrowserSubscription;

    private function createAlert(int $km, array $attributes = [], bool $subscribed = true): Alerta
    {
        $subscription = $subscribed ? $this->browserSubscription(bin2hex(random_bytes(8))) : null;
        $user = User::factory()->create(['push_subscription' => $subscription, 'push_endpoint_hash' => $subscription ? hash('sha256', $subscription['endpoint']) : null]);
        $moto = Motocicleta::create([
            'usuario_id' => $user->id, 'marca' => 'Honda', 'modelo' => 'CG',
            'ano' => 2025, 'placa' => 'TEST'.$user->id, 'km_atual' => $km,
        ]);

        return Alerta::create(array_merge([
            'motocicleta_id' => $moto->id, 'tipo_alerta' => 'troca_oleo',
            'nivel_urgencia' => 'baixa', 'km_limite' => 1000, 'status' => 'pendente',
        ], $attributes));
    }

    public function test_threshold_types_and_duplicate_prevention(): void
    {
        $below = $this->createAlert(899);
        $oil = $this->createAlert(900);
        $revision = $this->createAlert(1100, ['tipo_alerta' => 'revisao']);
        $ignored = $this->createAlert(1000, ['tipo_alerta' => 'pneus']);
        $done = $this->createAlert(1000, ['status' => 'concluido']);
        $noLimit = $this->createAlert(1000, ['km_limite' => null]);
        $zero = $this->createAlert(1000, ['km_limite' => 0]);
        $this->mock(WebPushService::class, function ($mock) use ($oil, $revision) {
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert, $subscription) => $alert->id === $oil->id && isset($subscription['keys']['p256dh']));
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert, $subscription) => $alert->id === $revision->id && isset($subscription['keys']['p256dh']));
        });
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('notificado', $oil->fresh()->status);
        $this->assertSame('media', $oil->fresh()->nivel_urgencia);
        $this->assertSame('alta', $revision->fresh()->nivel_urgencia);
        foreach ([$below, $ignored, $noLimit, $zero] as $alert) {
            $this->assertSame('pendente', $alert->fresh()->status);
        }
        $this->assertSame('concluido', $done->fresh()->status);
    }

    public function test_missing_subscription_keeps_alert_pending(): void
    {
        $alert = $this->createAlert(900, [], false);
        $this->mock(WebPushService::class)->shouldNotReceive('send');
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('pendente', $alert->fresh()->status);
    }

    public function test_failure_is_retried_and_does_not_block_other_alerts(): void
    {
        $failed = $this->createAlert(900);
        $other = $this->createAlert(900);
        $this->mock(WebPushService::class, function ($mock) use ($failed, $other) {
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert) => $alert->id === $failed->id)->andThrow(new RuntimeException('Push unavailable'));
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert) => $alert->id === $other->id);
        });
        $this->artisan('alertas:verificar')->assertFailed();
        $this->assertSame('pendente', $failed->fresh()->status);
        $this->assertSame('notificado', $other->fresh()->status);
        $this->mock(WebPushService::class)->shouldReceive('send')->once();
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('notificado', $failed->fresh()->status);
    }
}
