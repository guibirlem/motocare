<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    private function motorcycle(User $owner, string $plate = 'TST1A23')
    {
        return $owner->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2026, 'placa' => $plate, 'km_atual' => 1000]);
    }

    private function fuel(array $extra = []): array
    {
        return array_replace(['tipo_despesa' => 'combustivel', 'data' => '2026-10-23', 'litros' => '12.50', 'preco_litro' => '7.00', 'valor' => '87.50'], $extra);
    }

    public function test_default_delivery_exposes_finance_only_after_authentication(): void
    {
        $this->assertTrue(config('features.financeiro'));
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $this->getJson('/api/features')->assertUnauthorized();
        $this->getJson('/api/motocicletas/'.$moto->id.'/despesas')->assertUnauthorized();
        $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-10-31')->assertUnauthorized();
        $this->actingAs($owner)->getJson('/api/features')->assertOk()->assertExactJson(['financeiro' => true]);
        $this->getJson('/api/motocicletas/'.$moto->id.'/despesas')->assertOk();
    }

    public function test_rounding_tolerance_and_large_products_are_validated_without_overflow(): void
    {
        $owner = User::factory()->create();
        $path = '/api/motocicletas/'.$this->motorcycle($owner)->id.'/despesas';
        $this->actingAs($owner);
        foreach (['1.51', '1.52', '1.53'] as $total) {
            $this->postJson($path, $this->fuel(['litros' => '1.01', 'preco_litro' => '1.50', 'valor' => $total]))->assertCreated()->assertJsonPath('valor', $total);
        }
        foreach (['1.50', '1.54'] as $total) {
            $this->postJson($path, $this->fuel(['litros' => '1.01', 'preco_litro' => '1.50', 'valor' => $total]))->assertUnprocessable()->assertJsonValidationErrors('valor');
        }
        $this->postJson($path, $this->fuel(['litros' => '0.01', 'preco_litro' => '99999999.99', 'valor' => '1000000.00']))->assertCreated()->assertJsonPath('valor', '1000000.00');
        $this->postJson($path, $this->fuel(['litros' => '999999.99', 'preco_litro' => '99999999.99', 'valor' => '99999999.99']))->assertUnprocessable();
        $this->assertDatabaseCount('despesas', 4);
    }

    public function test_bad_amounts_and_malformed_fields_never_persist(): void
    {
        $owner = User::factory()->create();
        $path = '/api/motocicletas/'.$this->motorcycle($owner)->id.'/despesas';
        $this->actingAs($owner);
        foreach ([['valor' => '999999999.00'], ['valor' => '-0.01'], ['valor' => '1,00'], ['valor' => '1.001'], ['litros' => '0'], ['litros' => '1000000.00'], ['preco_litro' => '0'], ['preco_litro' => '1e2'], ['preco_litro' => ['invalid']], ['data' => '2026-02-30'], ['data' => '2026-10-23T00:00:00Z'], ['tipo_despesa' => 'reparo', 'descricao' => '   ']] as $invalid) {
            $this->postJson($path, $this->fuel($invalid))->assertUnprocessable();
        }
        $this->assertDatabaseCount('despesas', 0);
    }

    public function test_type_changes_clear_fields_and_expenses_never_change_mileage_or_alerts(): void
    {
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $alert = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_base' => 1000, 'km_limite' => 2000, 'nivel_urgencia' => 'media', 'status' => 'pendente']);
        $before = $alert->fresh()->getAttributes();
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->actingAs($owner);
        $id = $this->postJson($path, $this->fuel(['posto' => 'Posto inicial', 'descricao' => 'Viagem', 'km_atual' => 9999]))->assertCreated()->json('id');
        $this->putJson($path.'/'.$id, ['tipo_despesa' => 'reparo', 'descricao' => 'Espelho', 'data' => '2026-10-23', 'valor' => '12.34'])->assertOk()->assertJsonPath('litros', null)->assertJsonPath('preco_litro', null)->assertJsonPath('posto', null);
        $this->putJson($path.'/'.$id, $this->fuel())->assertOk()->assertJsonPath('descricao', null)->assertJsonPath('posto', null)->assertJsonPath('litros', '12.50');
        $this->deleteJson($path.'/'.$id)->assertNoContent();
        $this->assertSame(1000, $moto->fresh()->km_atual);
        $this->assertSame($before, $alert->fresh()->getAttributes());
        $this->assertDatabaseCount('manutencoes', 0);
    }

    public function test_expense_identifiers_cannot_cross_motorcycles_of_the_same_owner(): void
    {
        $owner = User::factory()->create();
        $first = $this->motorcycle($owner);
        $second = $this->motorcycle($owner, 'TST2B34');
        $record = $first->despesas()->create($this->fuel());
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$second->id.'/despesas/'.$record->id;
        $this->putJson($path, $this->fuel())->assertNotFound();
        $this->deleteJson($path)->assertNotFound();
        $this->assertDatabaseHas('despesas', ['id' => $record->id, 'motocicleta_id' => $first->id, 'valor' => '87.50']);
    }

    public function test_month_filter_is_inclusive_and_stably_orders_same_day_records(): void
    {
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $before = $moto->despesas()->create($this->fuel(['data' => '2024-01-31']));
        $first = $moto->despesas()->create($this->fuel(['data' => '2024-02-01']));
        $last = $moto->despesas()->create($this->fuel(['data' => '2024-02-29']));
        $newest = $moto->despesas()->create($this->fuel(['data' => '2024-02-29']));
        $moto->despesas()->create($this->fuel(['data' => '2024-03-01']));
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->actingAs($owner)->getJson($path.'?mes=2024-02')->assertOk()->assertJsonCount(3)->assertJsonPath('0.id', $newest->id)->assertJsonPath('1.id', $last->id)->assertJsonPath('2.id', $first->id)->assertJsonMissing(['id' => $before->id]);
        $this->getJson($path)->assertJsonCount(5);
        $this->getJson($path.'?mes=2024-02-01')->assertUnprocessable();
    }

    public function test_reports_keep_motorcycle_scope_and_count_completed_maintenance_once(): void
    {
        $owner = User::factory()->create();
        $first = $this->motorcycle($owner);
        $second = $this->motorcycle($owner, 'TST2B34');
        $other = $this->motorcycle(User::factory()->create(), 'TST3C45');
        $first->despesas()->create($this->fuel());
        $first->despesas()->create(['tipo_despesa' => 'reparo', 'descricao' => 'Espelho', 'valor' => '12.34', 'data' => '2026-10-01']);
        $first->manutencoes()->create(['tipo_servico' => 'freios', 'status' => 'concluido', 'valor' => '100.01', 'km_servico' => 1000, 'data' => '2026-10-01']);
        $second->despesas()->create($this->fuel(['litros' => '1.00', 'preco_litro' => '2.00', 'valor' => '2.00']));
        $second->manutencoes()->create(['tipo_servico' => 'pneus', 'status' => 'pendente', 'valor' => '500.00', 'data' => '2026-10-31']);
        $other->despesas()->create($this->fuel());
        $this->actingAs($owner);
        $path = '/api/relatorios?inicio=2026-10-01&fim=2026-12-31';
        $this->getJson($path.'&motocicleta_id='.$first->id)->assertOk()->assertJsonCount(1, 'motocicletas')->assertJsonCount(2, 'despesas')->assertJsonCount(1, 'manutencoes')->assertJsonPath('resumo.total', '199.85');
        $this->getJson($path)->assertOk()->assertJsonCount(2, 'motocicletas')->assertJsonCount(3, 'despesas')->assertJsonCount(2, 'manutencoes')->assertJsonPath('resumo.combustivel', '89.50')->assertJsonPath('resumo.reparos', '12.34')->assertJsonPath('resumo.manutencoes', '100.01')->assertJsonPath('resumo.total', '201.85')->assertJsonPath('mensal.0.total', '201.85')->assertJsonPath('mensal.1.total', '0.00')->assertJsonPath('mensal.2.total', '0.00');
    }

    public function test_invalid_motorcycle_filter_never_falls_back_to_all_motorcycles(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['0', '-1', 'invalid', '1.5'] as $id) {
            $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-10-31&motocicleta_id='.$id)->assertUnprocessable()->assertJsonValidationErrors('motocicleta_id');
        }
        $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-10-31&motocicleta_id=999')->assertNotFound();
    }

    public function test_authenticated_financial_mutations_require_real_csrf_tokens(): void
    {
        $owner = User::factory()->create();
        $moto = $this->motorcycle($owner);
        $record = $moto->despesas()->create($this->fuel());
        $this->actingAs($owner);
        $this->app->instance('env', 'local');
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->postJson($path, $this->fuel())->assertStatus(419);
        $this->putJson($path.'/'.$record->id, $this->fuel())->assertStatus(419);
        $this->deleteJson($path.'/'.$record->id)->assertStatus(419);
        $this->assertDatabaseCount('despesas', 1);
    }
}
