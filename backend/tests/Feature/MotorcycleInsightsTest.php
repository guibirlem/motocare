<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FuelStationMemory;
use App\Services\MotorcycleInsights;
use App\Services\WebPushService;
use App\Support\MotorcycleServices;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotorcycleInsightsTest extends TestCase
{
    use RefreshDatabase;

    private function pilot(string $email = 'insights@example.test')
    {
        return User::create(['nome' => 'Piloto', 'email' => $email, 'password' => 'password123']);
    }

    private function moto($user, string $plate = 'ABC1D23')
    {
        return $user->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2024, 'placa' => $plate, 'km_atual' => 1000, 'capacidade_tanque' => '12.00', 'volume_reserva' => '2.00']);
    }

    private function signed($user)
    {
        return $this->actingAs($user)->withSession(['password_hash_web' => $user->password]);
    }

    private function fuel(array $extra = []): array
    {
        return array_replace(['tipo_despesa' => 'combustivel', 'valor' => '60.00', 'litros' => '10.00', 'preco_litro' => '6.00', 'data' => '2026-10-01', 'km_abastecimento' => 1000, 'tanque_cheio' => true, 'combustivel' => 'gasolina'], $extra);
    }

    public function test_station_memory_is_per_owner_fuel_and_latest_date_and_recomputed_on_edit_delete(): void
    {
        config(['features.financeiro' => true]);
        $user = $this->pilot();
        $moto = $this->moto($user);
        $this->signed($user);
        $id = $this->postJson('/api/postos', ['nome' => '  Posto   Centro '])->assertCreated()->assertJsonPath('nome', 'Posto Centro')->json('id');
        $this->postJson('/api/postos', ['nome' => 'Posto Centro'])->assertUnprocessable();
        $this->postJson('/api/postos', ['nome' => []])->assertUnprocessable();
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $new = $this->postJson($path, $this->fuel(['posto_id' => $id, 'preco_litro' => '7.00', 'valor' => '70.00', 'data' => '2026-10-02']))->assertCreated()->json('id');
        $this->postJson($path, $this->fuel(['posto_id' => $id]))->assertCreated();
        $this->postJson($path, $this->fuel(['posto_id' => $id, 'combustivel' => 'etanol', 'preco_litro' => '4.00', 'valor' => '40.00']))->assertCreated();
        $this->getJson('/api/postos')->assertJsonPath('0.precos.0.preco_litro', '7.00')->assertJsonCount(2, '0.precos');
        $this->putJson($path.'/'.$new, $this->fuel(['posto_id' => $id, 'data' => '2026-10-02', 'preco_litro' => '7.10', 'valor' => '71.00']))->assertOk();
        $this->getJson('/api/postos')->assertJsonPath('0.precos.0.preco_litro', '7.10');
        $other = $this->pilot('other@example.test');
        $otherMoto = $this->moto($other, 'DEF2G34');
        $this->signed($other);
        $this->getJson('/api/postos')->assertExactJson([]);
        $this->postJson('/api/motocicletas/'.$otherMoto->id.'/despesas', $this->fuel(['posto_id' => $id]))->assertNotFound();
        $this->deleteJson('/api/postos/'.$id)->assertNotFound();
        $this->signed($user)->deleteJson($path.'/'.$new)->assertNoContent();
        $this->getJson('/api/postos')->assertJsonPath('0.precos.0.preco_litro', '6.00');
        $this->deleteJson('/api/postos/'.$id)->assertNoContent();
        $this->assertDatabaseHas('despesas', ['motocicleta_id' => $moto->id, 'posto_id' => null, 'posto' => 'Posto Centro', 'valor' => '60.00']);
    }

    public function test_full_tanks_include_partials_weighted_consumption_reserve_and_historical_odometer(): void
    {
        config(['features.financeiro' => true]);
        $user = $this->pilot();
        $moto = $this->moto($user);
        $this->signed($user);
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->postJson($path, $this->fuel(['litros' => '5.00', 'valor' => '30.00']))->assertCreated();
        $this->postJson($path, $this->fuel(['data' => '2026-10-03', 'km_abastecimento' => 1100, 'tanque_cheio' => false, 'litros' => '3.00', 'valor' => '18.00']))->assertCreated();
        $this->postJson($path, $this->fuel(['data' => '2026-10-05', 'km_abastecimento' => 1300, 'litros' => '7.00', 'valor' => '42.00']))->assertCreated();
        $this->assertSame(1300, (int) $moto->refresh()->km_atual);
        $this->postJson($path, $this->fuel(['data' => '2026-09-01', 'km_abastecimento' => 900]))->assertCreated();
        $this->assertSame(1300, (int) $moto->refresh()->km_atual);
        // Apenas o segundo ciclo: 300 km / (3 + 7) L. Primeiro trecho histórico: 100 / 10.
        $stats = app(MotorcycleInsights::class)->fuel($moto, '2026-10-03', '2026-10-06');
        $this->assertEquals(30, $stats['consumo_medio']);
        $this->assertCount(1, $stats['ciclos']);
        $moto->update(['km_atual' => 1360]);
        $stats = app(MotorcycleInsights::class)->fuel($moto);
        $this->assertEquals(26.67, $stats['consumo_medio']); // 400 km / 15 L; média simples seria 25.
        $this->assertEquals(60, $stats['reserva']['parcial_km']);
        $this->assertEquals(207, $stats['reserva']['km_ate_reserva']);
        $this->assertEquals(260, $stats['reserva']['km_ate_secar']);
        $this->postJson($path, $this->fuel(['data' => '2026-10-06', 'km_abastecimento' => 1400, 'tanque_cheio' => false]))->assertCreated();
        $moto->refresh()->update(['km_atual' => 1460]);
        $stats = app(MotorcycleInsights::class)->fuel($moto);
        $this->assertEquals(9.75, $stats['reserva']['litros_estimados']);
        $this->postJson($path, $this->fuel(['data' => today()->addDay()->toDateString()]))->assertUnprocessable();
        $this->postJson($path, $this->fuel(['km_abastecimento' => null]))->assertUnprocessable();
        $this->postJson($path, $this->fuel(['data' => '2026-10-07', 'km_abastecimento' => null, 'tanque_cheio' => null]))->assertCreated();
        $this->assertNull(app(MotorcycleInsights::class)->fuel($moto)['reserva']);
    }

    public function test_missing_and_regressing_readings_do_not_manufacture_consumption(): void
    {
        $user = $this->pilot();
        $moto = $this->moto($user);
        foreach ([1000, 900, 1200] as $index => $km) {
            $moto->despesas()->create($this->fuel(['data' => '2026-10-0'.($index + 1), 'km_abastecimento' => $km]));
        }
        $this->assertNull(app(MotorcycleInsights::class)->fuel($moto)['consumo_medio']);
        $moto->despesas()->create($this->fuel(['data' => '2026-10-04', 'km_abastecimento' => 1500]));
        $this->assertEquals(30, app(MotorcycleInsights::class)->fuel($moto)['consumo_medio']);
    }

    public function test_chain_counts_completed_adjustments_since_latest_kit_and_rain_is_idempotent_and_due(): void
    {
        $user = $this->pilot();
        $moto = $this->moto($user);
        $this->signed($user);
        $base = ['descricao' => null, 'valor' => '0.00', 'status' => 'concluido', 'data' => '2026-10-01', 'km_servico' => 1000];
        $path = '/api/motocicletas/'.$moto->id;
        $this->assertNull(app(MotorcycleInsights::class)->chain($moto)['ajustes_desde_troca']);
        $this->postJson($path.'/manutencoes', $base + ['tipo_servico' => 'kit_relacao'])->assertCreated();
        $adjustment = $this->postJson($path.'/manutencoes', array_replace($base, ['tipo_servico' => 'ajuste_corrente', 'data' => '2026-10-02', 'km_servico' => 1100]))->assertCreated()->json('id');
        $this->postJson($path.'/manutencoes', array_replace($base, ['tipo_servico' => 'ajuste_corrente', 'status' => 'pendente']))->assertCreated();
        $this->assertSame(1, app(MotorcycleInsights::class)->chain($moto)['ajustes_desde_troca']);
        $this->deleteJson($path.'/manutencoes/'.$adjustment)->assertNoContent();
        $this->assertSame(0, app(MotorcycleInsights::class)->chain($moto)['ajustes_desde_troca']);
        $id = $this->postJson($path.'/chuva-lavagem')->assertCreated()->assertJsonPath('tipo_alerta', 'lubrificacao_corrente')->json('id');
        $this->postJson($path.'/chuva-lavagem')->assertOk()->assertJsonPath('id', $id);
        $this->assertDatabaseCount('alertas', 1);
        $user->update(['push_subscription' => ['endpoint' => 'https://fcm.googleapis.com/test']]);
        $this->mock(WebPushService::class)->shouldReceive('send')->once();
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertDatabaseHas('alertas', ['id' => $id, 'status' => 'notificado']);
        $this->postJson($path.'/chuva-lavagem')->assertOk()->assertJsonPath('id', $id);
        $this->putJson($path.'/alertas/'.$id, ['status' => 'concluido'])->assertOk();
        $this->postJson($path.'/chuva-lavagem')->assertCreated();
        $this->assertDatabaseCount('alertas', 2);
    }

    public function test_new_categories_tank_validation_and_feature_disabled_preserve_legacy(): void
    {
        config(['features.financeiro' => true]);
        $user = $this->pilot();
        $moto = $this->moto($user);
        $this->signed($user);
        $path = '/api/motocicletas/'.$moto->id;
        $this->putJson($path, array_replace($moto->toArray(), ['volume_reserva' => '12.00']))->assertUnprocessable();
        $this->postJson($path.'/despesas', ['tipo_despesa' => 'impostos', 'valor' => '30.00', 'data' => '2026-10-01'])->assertCreated();
        $this->postJson($path.'/manutencoes', ['tipo_servico' => 'pneu_traseiro', 'natureza' => 'preventiva', 'valor' => '100.00', 'status' => 'concluido', 'data' => '2026-10-01', 'km_servico' => 1000])->assertCreated();
        $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-10-23')->assertOk()->assertJsonPath('resumo.total', '130.00')->assertJsonPath('categorias.impostos', '30.00')->assertJsonPath('categorias.preventiva', '100.00');
        $this->postJson($path.'/alertas', ['tipo_alerta' => 'pneu_traseiro', 'km_base' => 1000, 'km_limite' => 1500, 'data_limite' => '2026-10-30'])->assertCreated();
        $this->getJson($path.'/painel')->assertOk()->assertJsonPath('gasto_mes', '130.00')->assertJsonPath('consumo_medio', null);
        config(['features.financeiro' => false]);
        $this->getJson('/api/postos')->assertNotFound();
        $this->getJson($path.'/painel')->assertOk()->assertJsonMissingPath('despesas')->assertJsonMissingPath('consumo_medio')->assertJsonPath('gasto_mes', '100.00');
        $other = $this->pilot('second@example.test');
        $this->signed($other);
        $this->getJson($path.'/painel')->assertNotFound();
        $this->postJson($path.'/chuva-lavagem')->assertNotFound();
    }

    public function test_expense_odometer_and_station_memory_roll_back_together_and_deletion_never_rewinds(): void
    {
        config(['features.financeiro' => true]);
        $user = $this->pilot();
        $moto = $this->moto($user);
        $this->signed($user);
        $station = $user->postos()->create(['nome' => 'Posto teste']);
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->mock(FuelStationMemory::class)->shouldReceive('refresh')->once()->andThrow(new \RuntimeException('Falha simulada'));
        $this->postJson($path, $this->fuel(['posto_id' => $station->id, 'km_abastecimento' => 1500]))->assertStatus(500);
        $this->assertDatabaseCount('despesas', 0);
        $this->assertDatabaseCount('posto_precos', 0);
        $this->assertSame(1000, (int) $moto->refresh()->km_atual);
        $this->app->forgetInstance(FuelStationMemory::class);
        $id = $this->postJson($path, $this->fuel(['posto_id' => $station->id, 'km_abastecimento' => 1500]))->assertCreated()->json('id');
        $this->putJson($path.'/'.$id, ['tipo_despesa' => 'reparo', 'descricao' => 'Espelho', 'valor' => '10.00', 'data' => '2026-10-01'])->assertOk()->assertJsonPath('posto_id', null)->assertJsonPath('combustivel', null)->assertJsonPath('km_abastecimento', null)->assertJsonPath('tanque_cheio', null);
        $this->assertSame(1500, (int) $moto->refresh()->km_atual);
        $this->assertDatabaseCount('posto_precos', 0);
        $this->deleteJson($path.'/'.$id)->assertNoContent();
        $this->assertSame(1500, (int) $moto->refresh()->km_atual);
    }

    public function test_all_added_service_categories_persist_and_can_have_dual_reminders(): void
    {
        $user = $this->pilot();
        $moto = $this->moto($user);
        $this->signed($user);
        $path = '/api/motocicletas/'.$moto->id;
        foreach (array_keys(MotorcycleServices::LABELS) as $type) {
            $this->postJson($path.'/manutencoes', ['tipo_servico' => $type, 'natureza' => 'preventiva', 'valor' => '0.00', 'status' => 'concluido', 'data' => '2026-10-01', 'km_servico' => 1000])->assertCreated()->assertJsonPath('tipo_servico', $type);
            $this->postJson($path.'/alertas', ['tipo_alerta' => $type, 'km_base' => 1000, 'km_limite' => 1500, 'data_limite' => '2026-10-30'])->assertCreated()->assertJsonPath('tipo_alerta', $type);
        }
        $this->assertDatabaseCount('manutencoes', count(MotorcycleServices::LABELS));
        $this->assertDatabaseCount('alertas', count(MotorcycleServices::LABELS));
    }
}
