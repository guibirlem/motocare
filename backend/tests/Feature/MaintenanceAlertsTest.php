<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Motocicleta;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MaintenanceAlertsTest extends TestCase
{
    use RefreshDatabase;

    private function createAlert(int $km, array $attributes = [], ?string $token = 'device-token'): Alerta
    {
        $user = User::factory()->create(['fcm_token' => $token]);
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
        $this->mock(FcmService::class, function ($mock) use ($oil, $revision) {
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert, $token) => $alert->id === $oil->id && $token === 'device-token');
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert, $token) => $alert->id === $revision->id && $token === 'device-token');
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

    public function test_missing_token_keeps_alert_pending(): void
    {
        $alert = $this->createAlert(900, [], null);
        $this->mock(FcmService::class)->shouldNotReceive('send');
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('pendente', $alert->fresh()->status);
    }

    public function test_failure_is_retried_and_does_not_block_other_alerts(): void
    {
        $failed = $this->createAlert(900);
        $other = $this->createAlert(900);
        $this->mock(FcmService::class, function ($mock) use ($failed, $other) {
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert) => $alert->id === $failed->id)->andThrow(new RuntimeException('FCM unavailable'));
            $mock->shouldReceive('send')->once()->withArgs(fn ($alert) => $alert->id === $other->id);
        });
        $this->artisan('alertas:verificar')->assertFailed();
        $this->assertSame('pendente', $failed->fresh()->status);
        $this->assertSame('notificado', $other->fresh()->status);
        $this->mock(FcmService::class)->shouldReceive('send')->once();
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('notificado', $failed->fresh()->status);
    }
}
