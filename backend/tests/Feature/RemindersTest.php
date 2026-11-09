<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemindersTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\BrowserSubscription;

    private function motorcycle(User $owner)
    {
        return $owner->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2026, 'placa' => 'RMD1D23', 'km_atual' => 1450]);
    }

    public function test_calendar_reminders_persist_and_edit_complete_delete_without_changing_domain_records(): void
    {
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$moto->id.'/alertas';
        foreach (['seguro', 'ipva', 'licenciamento'] as $type) {
            $id = $this->postJson($path, ['tipo_alerta' => $type, 'data_limite' => '2027-02-10', 'km_limite' => null, 'km_base' => null])
                ->assertCreated()->assertJsonPath('tipo_alerta', $type)->assertJsonPath('km_limite', null)->assertJsonPath('km_base', null)->json('id');
            $this->putJson($path.'/'.$id, ['tipo_alerta' => $type, 'data_limite' => '2027-03-10'])->assertOk()->assertJsonPath('data_limite', '2027-03-10');
            $this->putJson($path.'/'.$id, ['status' => 'concluido'])->assertOk()->assertJsonPath('status', 'concluido');
            $this->putJson($path.'/'.$id, ['tipo_alerta' => $type, 'data_limite' => '2028-03-10'])->assertUnprocessable();
            $this->deleteJson($path.'/'.$id)->assertNoContent();
        }
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('manutencoes', 0);
        $this->assertDatabaseCount('despesas', 0);
        $this->assertSame(1450, $moto->fresh()->km_atual);
    }

    public function test_calendar_types_require_valid_dates_and_cannot_use_mileage(): void
    {
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$moto->id.'/alertas';
        foreach (['seguro', 'ipva', 'licenciamento'] as $type) {
            $this->postJson($path, ['tipo_alerta' => $type, 'km_limite' => 2000])->assertUnprocessable()->assertJsonValidationErrors(['data_limite', 'km_limite']);
            $this->postJson($path, ['tipo_alerta' => $type, 'data_limite' => '2026-02-30'])->assertUnprocessable()->assertJsonValidationErrors('data_limite');
            $this->postJson($path, ['tipo_alerta' => $type, 'data_limite' => '2027-01-01', 'km_base' => 1000])->assertUnprocessable()->assertJsonValidationErrors('km_base');
        }
        $this->assertDatabaseCount('alertas', 0);
        $id = $this->postJson($path, ['tipo_alerta' => 'abastecimento', 'km_base' => 1450, 'km_limite' => 1800])->assertCreated()->json('id');
        $this->putJson($path.'/'.$id, ['tipo_alerta' => 'seguro', 'km_base' => null, 'km_limite' => null, 'data_limite' => '2027-01-01'])
            ->assertOk()->assertJsonPath('km_limite', null)->assertJsonPath('km_base', null);
    }

    public function test_scheduler_includes_new_types_without_sending_early_or_duplicating_notifications(): void
    {
        $subscription = $this->browserSubscription('isolated-reminders');
        $owner = User::factory()->create(['push_subscription' => $subscription]);
        $moto = $this->motorcycle($owner);
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$moto->id.'/alertas';
        $due = [];
        foreach (['seguro', 'ipva', 'licenciamento'] as $type) {
            $due[] = $this->postJson($path, ['tipo_alerta' => $type, 'data_limite' => today()->toDateString()])->assertCreated()->json('id');
        }
        $due[] = $this->postJson($path, ['tipo_alerta' => 'abastecimento', 'km_base' => 1000, 'km_limite' => 1500])->assertCreated()->json('id');
        $future = $this->postJson($path, ['tipo_alerta' => 'seguro', 'data_limite' => today()->addDay()->toDateString()])->assertCreated()->json('id');
        $this->mock(WebPushService::class, function ($mock) use ($due) {
            foreach ($due as $id) {
                $mock->shouldReceive('send')->once()->withArgs(fn ($alert, $subscription) => $alert->id === $id && isset($subscription['keys']));
            }
        });
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->artisan('alertas:verificar')->assertSuccessful();
        foreach ($due as $id) {
            $this->assertDatabaseHas('alertas', ['id' => $id, 'status' => 'notificado']);
        }
        $this->assertDatabaseHas('alertas', ['id' => $future, 'status' => 'pendente']);
        $this->assertDatabaseCount('despesas', 0);
    }

    public function test_reminders_keep_account_ownership_and_csrf_protection(): void
    {
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $alert = $moto->alertas()->create(['tipo_alerta' => 'ipva', 'data_limite' => '2027-01-01', 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $path = '/api/motocicletas/'.$moto->id.'/alertas';
        $this->getJson($path)->assertUnauthorized();
        $other = User::factory()->create();
        $this->actingAs($other);
        $this->getJson($path)->assertNotFound();
        $this->postJson($path, ['tipo_alerta' => 'seguro', 'data_limite' => '2027-01-01'])->assertNotFound();
        $this->putJson($path.'/'.$alert->id, ['status' => 'concluido'])->assertNotFound();
        $this->deleteJson($path.'/'.$alert->id)->assertNotFound();
        $this->actingAs($owner);
        $this->app->instance('env', 'local');
        $this->postJson($path, ['tipo_alerta' => 'seguro', 'data_limite' => '2027-01-01'])->assertStatus(419);
        $this->assertDatabaseCount('alertas', 1);
    }
}
