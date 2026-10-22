<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AlertIntervalTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\BrowserSubscription;

    private function scenario(): array
    {
        $user = User::factory()->create();
        $subscription = $this->browserSubscription();
        $user->update(['push_subscription' => $subscription, 'push_endpoint_hash' => hash('sha256', $subscription['endpoint'])]);
        $moto = $user->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2026, 'placa' => 'INT1D23', 'km_atual' => 66779]);
        $this->actingAs($user);

        return [$user, $moto, '/api/motocicletas/'.$moto->id.'/alertas'];
    }

    public function test_relative_interval_does_not_notify_at_creation_and_sends_exactly_at_ninety_percent(): void
    {
        [$user,$moto,$path] = $this->scenario();
        $id = $this->postJson($path, ['tipo_alerta' => 'revisao', 'km_limite' => 71779])->assertCreated()->assertJsonPath('km_base', 66779)->json('id');
        $this->mock(WebPushService::class)->shouldNotReceive('send');
        $this->artisan('alertas:verificar')->assertSuccessful();
        $moto->update(['km_atual' => 71278]);
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('pendente', $moto->alertas()->find($id)->status);
        $moto->update(['km_atual' => 71279]);
        $this->mock(WebPushService::class, fn ($mock) => $mock->shouldReceive('send')->once()->withArgs(fn ($alert) => $alert->id === $id));
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->artisan('alertas:verificar')->assertSuccessful();
        $alert = $moto->alertas()->find($id);
        $this->assertSame('notificado', $alert->status);
        $this->assertSame('media', $alert->nivel_urgencia);
    }

    public function test_reference_is_validated_preserved_when_omitted_and_cleared_for_date_only(): void
    {
        [$user,$moto,$path] = $this->scenario();
        foreach ([-1, 71779, 66780, 4294967296] as $base) {
            $this->postJson($path, ['tipo_alerta' => 'troca_oleo', 'km_limite' => 71779, 'km_base' => $base])->assertUnprocessable()->assertJsonValidationErrors('km_base');
        }
        $id = $this->postJson($path, ['tipo_alerta' => 'troca_oleo', 'km_limite' => 71779, 'km_base' => 65000])->assertCreated()->json('id');
        $this->putJson($path.'/'.$id, ['tipo_alerta' => 'troca_oleo', 'km_limite' => 72000])->assertOk()->assertJsonPath('km_base', 65000);
        $this->putJson($path.'/'.$id, ['tipo_alerta' => 'troca_oleo', 'data_limite' => today()->addDay()->toDateString()])->assertOk()->assertJsonPath('km_base', null)->assertJsonPath('km_limite', null);
        $this->postJson($path, ['tipo_alerta' => 'revisao', 'data_limite' => today()->toDateString(), 'km_base' => 100])->assertUnprocessable();
    }

    public function test_expired_date_and_already_reached_kilometer_limit_still_notify(): void
    {
        [$user,$moto,$path] = $this->scenario();
        $first = $this->postJson($path, ['tipo_alerta' => 'revisao', 'km_limite' => 71779, 'data_limite' => today()->toDateString()])->assertCreated()->json('id');
        $second = $this->postJson($path, ['tipo_alerta' => 'troca_oleo', 'km_limite' => 66000])->assertCreated()->assertJsonPath('km_base', 0)->json('id');
        $this->mock(WebPushService::class, fn ($mock) => $mock->shouldReceive('send')->twice());
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('alta', $moto->alertas()->find($first)->nivel_urgencia);
        $this->assertSame('alta', $moto->alertas()->find($second)->nivel_urgencia);
    }

    public function test_aditive_migration_sets_legacy_references_without_changing_status_or_limits(): void
    {
        [$user,$moto,$path] = $this->scenario();
        $future = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_limite' => 71779, 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $overdue = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_limite' => 66000, 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $sent = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_limite' => 71779, 'status' => 'notificado', 'nivel_urgencia' => 'media']);
        $dateOnly = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'data_limite' => today(), 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $migration = require database_path('migrations/2026_10_22_000001_add_km_base_to_alertas_table.php');
        try {
            $migration->down();
            $migration->up();
            $this->assertSame(66779, $future->fresh()->km_base);
            $this->assertSame(0, $overdue->fresh()->km_base);
            $this->assertSame(0, $sent->fresh()->km_base);
            $this->assertSame('notificado', $sent->fresh()->status);
            $this->assertSame(71779, $future->fresh()->km_limite);
            $this->assertNull($dateOnly->fresh()->km_base);
        } finally {
            if (! Schema::hasColumn('alertas', 'km_base')) {
                $migration->up();
            }
        }
    }
}
