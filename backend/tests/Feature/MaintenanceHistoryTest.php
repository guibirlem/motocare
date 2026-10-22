<?php

namespace Tests\Feature;

use App\Models\Motocicleta;
use App\Models\User;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceHistoryTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\BrowserSubscription;

    private function user(string $email = 'owner@example.com'): User
    {
        return User::create(['nome' => 'Piloto', 'email' => $email, 'password' => 'password123']);
    }

    private function moto(User $user, string $placa = 'ABC1D23')
    {
        return $user->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2024, 'placa' => $placa, 'km_atual' => 1000]);
    }

    private function payload(array $extra = []): array
    {
        return array_replace(['tipo_servico' => 'troca_oleo', 'descricao' => 'Óleo', 'valor' => '125.90', 'status' => 'concluido', 'data' => '2026-10-19', 'km_servico' => 1000], $extra);
    }

    public function test_guests_receive_json_unauthorized(): void
    {
        $this->get('/api/motocicletas')->assertUnauthorized()->assertJsonStructure(['message']);
        $this->postJson('/api/motocicletas/1/manutencoes', $this->payload())->assertUnauthorized();
    }

    public function test_registration_login_and_logout(): void
    {
        $this->postJson('/api/register', ['nome' => 'Piloto', 'email' => 'pilot@example.com', 'password' => 'password123', 'password_confirmation' => 'password123'])
            ->assertCreated()->assertJsonPath('nome', 'Piloto')->assertJsonMissingPath('password');
        $this->getJson('/api/user')->assertOk();
        $this->postJson('/api/logout')->assertOk();
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/login', ['email' => 'pilot@example.com', 'password' => 'wrong'])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => 'pilot@example.com', 'password' => 'password123'])->assertOk();
        $this->getJson('/api/user')->assertOk();
    }

    public function test_motorcycle_owner_is_taken_from_session(): void
    {
        $owner = $this->user();
        $other = $this->user('other@example.com');
        $this->moto($other);
        $response = $this->actingAs($owner)->postJson('/api/motocicletas', ['usuario_id' => $other->id, 'marca' => 'Yamaha', 'modelo' => 'Factor', 'ano' => 2025, 'placa' => 'DEF2G34', 'km_atual' => 0]);
        $response->assertCreated()->assertJsonPath('usuario_id', $owner->id);
        $this->getJson('/api/motocicletas')->assertJsonCount(1)->assertJsonPath('0.placa', 'DEF2G34');
    }

    public function test_persistence_update_delete_and_decimal_contract(): void
    {
        $owner = $this->user();
        $moto = $this->moto($owner);
        $path = '/api/motocicletas/'.$moto->id.'/manutencoes';
        $this->actingAs($owner);
        $response = $this->postJson($path, $this->payload());
        $response->assertCreated()->assertJsonPath('valor', '125.90')->assertJsonPath('data', '2026-10-19');
        $id = $response->json('id');
        $this->assertDatabaseHas('manutencoes', ['id' => $id, 'motocicleta_id' => $moto->id]);
        $this->getJson($path)->assertJsonCount(1)->assertJsonPath('0.id', $id);
        $this->putJson($path.'/'.$id, $this->payload(['tipo_servico' => 'freios', 'valor' => '200.01']))->assertOk()->assertJsonPath('valor', '200.01');
        $this->assertEquals(1000, $moto->refresh()->km_atual);
        $this->deleteJson($path.'/'.$id)->assertNoContent();
        $this->assertDatabaseMissing('manutencoes', ['id' => $id]);
        $this->getJson($path)->assertExactJson([]);
    }

    public function test_other_users_and_wrong_motorcycles_cannot_access_records(): void
    {
        $owner = $this->user();
        $other = $this->user('other@example.com');
        $moto = $this->moto($owner);
        $second = $this->moto($owner, 'DEF2G34');
        $record = $moto->manutencoes()->create($this->payload());
        $path = '/api/motocicletas/'.$moto->id.'/manutencoes';
        $this->actingAs($other)->getJson($path)->assertNotFound();
        $this->postJson($path, $this->payload())->assertNotFound();
        $this->putJson($path.'/'.$record->id, $this->payload())->assertNotFound();
        $this->deleteJson($path.'/'.$record->id)->assertNotFound();
        $this->actingAs($owner)->withSession(['password_hash_web' => $owner->password])->putJson('/api/motocicletas/'.$second->id.'/manutencoes/'.$record->id, $this->payload())->assertNotFound();
        $this->assertDatabaseCount('manutencoes', 1);
    }

    public function test_validation_and_supported_services(): void
    {
        $owner = $this->user();
        $moto = $this->moto($owner);
        $path = '/api/motocicletas/'.$moto->id.'/manutencoes';
        $this->actingAs($owner);
        foreach (['troca_oleo', 'kit_relacao', 'pneus', 'freios'] as $type) {
            $this->postJson($path, $this->payload(['tipo_servico' => $type, 'status' => 'pendente', 'km_servico' => null]))->assertCreated();
        }
        foreach ([['tipo_servico' => 'outro'], ['valor' => '-1'], ['valor' => '1.001'], ['valor' => '100000000'], ['status' => 'Feito'], ['data' => '2026-02-30'], ['km_servico' => null], ['km_servico' => -1]] as $invalid) {
            $this->postJson($path, $this->payload($invalid))->assertUnprocessable();
        }
        $this->assertDatabaseCount('manutencoes', 4);
    }

    public function test_update_replaces_optional_fields_without_affecting_alerts(): void
    {
        $owner = $this->user();
        $moto = $this->moto($owner);
        $this->actingAs($owner);
        $record = $moto->manutencoes()->create($this->payload());
        $alert = $moto->alertas()->create(['tipo_alerta' => 'troca_oleo', 'km_limite' => 2000, 'nivel_urgencia' => 'media', 'status' => 'notificado']);
        $this->putJson('/api/motocicletas/'.$moto->id.'/manutencoes/'.$record->id, [
            'tipo_servico' => 'pneus', 'valor' => '0', 'status' => 'pendente', 'data' => '2026-10-20',
        ])->assertOk()->assertJsonPath('descricao', null)->assertJsonPath('km_servico', null)->assertJsonPath('valor', '0.00');
        $this->assertSame('notificado', $alert->refresh()->status);
        $this->assertEquals(1000, $moto->refresh()->km_atual);
    }

    public function test_completed_maintenance_advances_odometer_but_history_pending_edits_and_deletes_never_lower_it(): void
    {
        $owner = $this->user();
        $moto = $this->moto($owner);
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$moto->id.'/manutencoes';
        $alert = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_limite' => 2000, 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $id = $this->postJson($path, $this->payload(['km_servico' => 1800]))->assertCreated()->json('id');
        $this->assertEquals(1800, $moto->fresh()->km_atual);
        $this->postJson($path, $this->payload(['km_servico' => 500]))->assertCreated();
        $this->assertEquals(1800, $moto->fresh()->km_atual);
        $pending = $this->postJson($path, $this->payload(['status' => 'pendente', 'km_servico' => 2500]))->assertCreated()->json('id');
        $this->assertEquals(1800, $moto->fresh()->km_atual);
        $this->putJson($path.'/'.$pending, $this->payload(['km_servico' => 2500]))->assertOk();
        $this->assertEquals(2500, $moto->fresh()->km_atual);
        $this->putJson($path.'/'.$pending, $this->payload(['km_servico' => 2200]))->assertOk();
        $this->deleteJson($path.'/'.$pending)->assertNoContent();
        $this->deleteJson($path.'/'.$id)->assertNoContent();
        $this->assertEquals(2500, $moto->fresh()->km_atual);
        $this->assertSame('pendente', $alert->fresh()->status);
    }

    public function test_future_completed_maintenance_cannot_advance_odometer_or_overwrite_a_record(): void
    {
        $owner = $this->user();
        $moto = $this->moto($owner);
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$moto->id.'/manutencoes';
        $future = today()->addDay()->toDateString();
        $this->postJson($path, $this->payload(['data' => $future, 'km_servico' => 9000]))->assertUnprocessable()->assertJsonValidationErrors('data');
        $id = $this->postJson($path, $this->payload(['status' => 'pendente', 'data' => $future, 'km_servico' => 9000]))->assertCreated()->json('id');
        $this->putJson($path.'/'.$id, $this->payload(['data' => $future, 'km_servico' => 9000]))->assertUnprocessable();
        $this->assertDatabaseHas('manutencoes', ['id' => $id, 'status' => 'pendente']);
        $this->assertEquals(1000, $moto->fresh()->km_atual);
    }

    public function test_maintenance_and_odometer_are_rolled_back_together_on_failure(): void
    {
        $owner = $this->user();
        $moto = $this->moto($owner);
        $this->actingAs($owner);
        Motocicleta::updating(fn () => throw new \RuntimeException('isolated failure'));
        try {
            $this->postJson('/api/motocicletas/'.$moto->id.'/manutencoes', $this->payload(['km_servico' => 2000]))->assertStatus(500);
            $this->assertDatabaseCount('manutencoes', 0);
            $this->assertEquals(1000, $moto->fresh()->km_atual);
        } finally {
            Motocicleta::flushEventListeners();
        }
    }

    public function test_completed_maintenance_updates_the_odometer_used_by_the_next_scheduler_check(): void
    {
        $owner = $this->user();
        $subscription = $this->browserSubscription();
        $owner->update(['push_subscription' => $subscription, 'push_endpoint_hash' => hash('sha256', $subscription['endpoint'])]);
        $moto = $this->moto($owner);
        $alert = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_limite' => 2000, 'data_limite' => today()->addMonth()->toDateString(), 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $this->actingAs($owner)->postJson('/api/motocicletas/'.$moto->id.'/manutencoes', $this->payload(['km_servico' => 1800]))->assertCreated();
        $this->assertSame('pendente', $alert->fresh()->status);
        $this->mock(WebPushService::class, fn ($mock) => $mock->shouldReceive('send')->once()->withArgs(fn ($sent, $received) => $sent->id === $alert->id && (int) $sent->motocicleta->km_atual === 1800 && $received === $subscription));
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('notificado', $alert->fresh()->status);
    }
}
