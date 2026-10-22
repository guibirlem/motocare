<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WebPushService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\BrowserSubscription;

    private function pilot(string $email = 'pilot@example.com'): User
    {
        return User::create(['nome' => 'Piloto', 'email' => $email, 'password' => 'password123']);
    }

    private function moto(User $user, string $plate = 'ABC1D23')
    {
        return $user->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2024, 'placa' => $plate, 'km_atual' => 1000]);
    }

    private function signed(User $user)
    {
        return $this->actingAs($user)->withSession(['password_hash_web' => $user->password]);
    }

    private function fuel(array $extra = []): array
    {
        return array_replace(['tipo_despesa' => 'combustivel', 'valor' => '87.50', 'litros' => '12.50', 'preco_litro' => '7.00', 'posto' => 'Posto', 'data' => '2026-10-19'], $extra);
    }

    public function test_expense_crud_and_transition_to_repair(): void
    {
        config(['features.financeiro' => true]);
        $owner = $this->pilot();
        $moto = $this->moto($owner);
        $this->signed($owner);
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $r = $this->postJson($path, $this->fuel());
        $r->assertCreated()->assertJsonPath('valor', '87.50')->assertJsonPath('litros', '12.50');
        $id = $r->json('id');
        $this->assertDatabaseHas('despesas', ['id' => $id, 'motocicleta_id' => $moto->id]);
        $this->getJson($path.'?mes=2026-10')->assertJsonCount(1);
        $this->getJson($path.'?mes=2026-09')->assertExactJson([]);
        $this->putJson($path.'/'.$id, $this->fuel(['tipo_despesa' => 'reparo', 'descricao' => 'Pneu', 'valor' => '200.01']))->assertOk()->assertJsonPath('litros', null)->assertJsonPath('preco_litro', null)->assertJsonPath('posto', null)->assertJsonPath('descricao', 'Pneu');
        $this->deleteJson($path.'/'.$id)->assertNoContent();
        $this->assertDatabaseCount('despesas', 0);
    }

    public function test_expense_validation_and_rounding(): void
    {
        config(['features.financeiro' => true]);
        $owner = $this->pilot();
        $moto = $this->moto($owner);
        $this->signed($owner);
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        foreach ([['valor' => '1.00'], ['valor' => '87.501'], ['litros' => 0], ['preco_litro' => null], ['tipo_despesa' => 'outro'], ['data' => '2026-02-30'], ['tipo_despesa' => 'reparo', 'descricao' => ''], ['valor' => '-1'], ['litros' => '1e2']] as $invalid) {
            $this->postJson($path, $this->fuel($invalid))->assertUnprocessable();
        }
        $this->postJson($path, $this->fuel(['litros' => '1.01', 'preco_litro' => '1.50', 'valor' => '1.52']))->assertCreated();
        $this->postJson($path, $this->fuel(['litros' => '1.01', 'preco_litro' => '1.50', 'valor' => '1.49']))->assertUnprocessable();
        $this->getJson($path.'?mes=2026-13')->assertUnprocessable();
        $this->assertDatabaseCount('despesas', 1);
        $this->assertSame('0.30', Money::decimal(Money::cents('0.10') + Money::cents('0.20')));
    }

    public function test_fuel_replacement_clears_an_omitted_optional_station(): void
    {
        config(['features.financeiro' => true]);
        $owner = $this->pilot();
        $moto = $this->moto($owner);
        $this->signed($owner);
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $id = $this->postJson($path, $this->fuel())->assertCreated()->json('id');
        $replacement = $this->fuel();
        unset($replacement['posto']);
        $this->putJson($path.'/'.$id, $replacement)->assertOk()->assertJsonPath('posto', null);
    }

    public function test_every_expense_and_report_respects_ownership(): void
    {
        config(['features.financeiro' => true]);
        $owner = $this->pilot();
        $other = $this->pilot('other@example.com');
        $moto = $this->moto($owner);
        $second = $this->moto($other, 'DEF2G34');
        $record = $moto->despesas()->create($this->fuel());
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->signed($other)->getJson($path)->assertNotFound();
        $this->postJson($path, $this->fuel())->assertNotFound();
        $this->putJson($path.'/'.$record->id, $this->fuel())->assertNotFound();
        $this->deleteJson($path.'/'.$record->id)->assertNotFound();
        $this->putJson('/api/motocicletas/'.$second->id.'/despesas/'.$record->id, $this->fuel())->assertNotFound();
        $this->getJson('/api/relatorios?inicio=2026-01-01&fim=2026-12-31&motocicleta_id='.$moto->id)->assertNotFound();
        $this->getJson('/api/relatorios?inicio=2026-01-01&fim=2026-12-31')->assertOk()->assertJsonCount(0, 'despesas')->assertJsonPath('resumo.total', '0.00');
        $this->assertDatabaseHas('despesas', ['id' => $record->id, 'valor' => '87.50']);
    }

    public function test_monthly_summary_boundaries_precision_and_pending(): void
    {
        config(['features.financeiro' => true]);
        $owner = $this->pilot();
        $moto = $this->moto($owner);
        $this->signed($owner);
        foreach ([['2026-09-30', '999.00'], ['2026-10-01', '0.10'], ['2026-10-31', '0.20'], ['2026-11-01', '999.00']] as [$date,$value]) {
            $moto->despesas()->create(['tipo_despesa' => 'reparo', 'descricao' => 'Reparo', 'valor' => $value, 'data' => $date]);
        }
        $moto->manutencoes()->create(['tipo_servico' => 'freios', 'valor' => '100.01', 'status' => 'concluido', 'data' => '2026-10-10', 'km_servico' => 1000]);
        $moto->manutencoes()->create(['tipo_servico' => 'pneus', 'valor' => '500', 'status' => 'pendente', 'data' => '2026-10-11']);
        $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-10-31')->assertOk()->assertJsonCount(2, 'despesas')->assertJsonPath('resumo.despesas', '0.30')->assertJsonPath('resumo.manutencoes', '100.01')->assertJsonPath('resumo.total', '100.31')->assertJsonPath('mensal.0.total', '100.31');
        $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-12-31')->assertJsonCount(3, 'mensal')->assertJsonPath('mensal.2.total', '0.00');
        $this->getJson('/api/relatorios?inicio=2026-10-01&fim=2026-09-01')->assertUnprocessable();
        $this->getJson('/api/relatorios?inicio=2000-01-01&fim=2026-01-01')->assertUnprocessable();
    }

    public function test_motorcycle_updates_cannot_lower_odometer_and_cascade_only_owned_data(): void
    {
        $owner = $this->pilot();
        $other = $this->pilot('other@example.com');
        $moto = $this->moto($owner);
        $this->signed($owner);
        $path = '/api/motocicletas/'.$moto->id;
        $payload = $moto->only(['marca', 'modelo', 'placa', 'ano', 'km_atual']);
        $this->putJson($path, $payload)->assertOk();
        $this->putJson($path, [...$payload, 'km_atual' => 999])->assertUnprocessable();
        $this->putJson($path, [...$payload, 'km_atual' => 2000, 'usuario_id' => $other->id])->assertOk()->assertJsonPath('usuario_id', $owner->id);
        $moto->despesas()->create($this->fuel());
        $moto->manutencoes()->create(['tipo_servico' => 'freios', 'valor' => 100, 'status' => 'pendente', 'data' => '2026-10-19']);
        $moto->alertas()->create(['tipo_alerta' => 'revisao', 'nivel_urgencia' => 'media', 'status' => 'pendente', 'km_limite' => 3000]);
        $this->signed($other)->putJson($path, $payload)->assertNotFound();
        $this->deleteJson($path)->assertNotFound();
        $this->signed($owner)->deleteJson($path)->assertNoContent();
        $this->assertDatabaseCount('despesas', 0);
        $this->assertDatabaseCount('manutencoes', 0);
        $this->assertDatabaseCount('alertas', 0);
    }

    public function test_profile_password_and_session_revocation(): void
    {
        $owner = $this->pilot();
        $this->signed($owner);
        $this->putJson('/api/user', ['nome' => 'Novo nome', 'email' => $owner->email, 'telefone' => '123'])->assertOk()->assertJsonPath('nome', 'Novo nome');
        $this->putJson('/api/user', ['nome' => 'Novo', 'email' => 'new@example.com'])->assertUnprocessable();
        $this->putJson('/api/user', ['nome' => 'Novo', 'email' => 'new@example.com', 'current_password' => 'password123'])->assertOk();
        $oldHash = $owner->password;
        $this->putJson('/api/password', ['current_password' => 'wrong', 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'])->assertUnprocessable();
        $this->putJson('/api/password', ['current_password' => 'password123', 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'])->assertOk();
        $this->getJson('/api/user')->assertOk();
        $owner->refresh();
        $this->actingAs($owner)->withSession(['password_hash_web' => $oldHash])->getJson('/api/user')->assertUnauthorized();
    }

    public function test_alert_lifecycle_and_ownership(): void
    {
        $owner = $this->pilot();
        $other = $this->pilot('other@example.com');
        $moto = $this->moto($owner);
        $path = '/api/motocicletas/'.$moto->id.'/alertas';
        $this->signed($owner);
        $this->postJson($path, ['tipo_alerta' => 'revisao'])->assertUnprocessable();
        $this->postJson($path, ['tipo_alerta' => 'outro', 'km_limite' => 3000])->assertUnprocessable();
        $r = $this->postJson($path, ['tipo_alerta' => 'revisao', 'data_limite' => '2026-10-19', 'status' => 'notificado']);
        $r->assertCreated()->assertJsonPath('status', 'pendente');
        $id = $r->json('id');
        $this->putJson($path.'/'.$id, ['tipo_alerta' => 'troca_oleo', 'km_limite' => 3000])->assertOk()->assertJsonPath('data_limite', null);
        $moto->alertas()->find($id)->update(['status' => 'notificado']);
        $this->putJson($path.'/'.$id, ['tipo_alerta' => 'troca_oleo', 'km_limite' => 4000])->assertUnprocessable();
        $this->putJson($path.'/'.$id, ['status' => 'concluido'])->assertOk()->assertJsonPath('status', 'concluido');
        $this->signed($other)->getJson($path)->assertNotFound();
        $this->postJson($path, ['tipo_alerta' => 'revisao', 'km_limite' => 3000])->assertNotFound();
        $this->putJson($path.'/'.$id, ['status' => 'concluido'])->assertNotFound();
        $this->deleteJson($path.'/'.$id)->assertNotFound();
        $this->signed($owner)->deleteJson($path.'/'.$id)->assertNoContent();
    }

    public function test_due_date_sends_and_future_date_stays_pending(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 19)->startOfDay());
        $owner = $this->pilot();
        $subscription = $this->browserSubscription();
        $owner->update(['push_subscription' => $subscription, 'push_endpoint_hash' => hash('sha256', $subscription['endpoint'])]);
        $moto = $this->moto($owner);
        $due = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'data_limite' => '2026-10-19', 'nivel_urgencia' => 'media', 'status' => 'pendente']);
        $future = $moto->alertas()->create(['tipo_alerta' => 'troca_oleo', 'data_limite' => '2026-10-20', 'nivel_urgencia' => 'media', 'status' => 'pendente']);
        $this->mock(WebPushService::class, fn ($mock) => $mock->shouldReceive('send')->once()->withArgs(fn ($alert, $received) => $alert->id === $due->id && $received === $subscription));
        $this->artisan('alertas:verificar')->assertSuccessful();
        $this->assertSame('notificado', $due->refresh()->status);
        $this->assertSame('alta', $due->nivel_urgencia);
        $this->assertSame('pendente', $future->refresh()->status);
        $this->travelBack();
    }

    public function test_finance_is_deferred_by_default(): void
    {
        $this->assertFalse(config('features.financeiro'));
        $owner = $this->pilot();
        $moto = $this->moto($owner);
        $this->signed($owner);
        $path = '/api/motocicletas/'.$moto->id.'/despesas';
        $this->getJson('/api/relatorios?inicio=2026-01-01&fim=2026-12-31')->assertNotFound();
        $this->getJson($path)->assertNotFound();
        $this->postJson($path, $this->fuel())->assertNotFound();
        $this->putJson($path.'/1', $this->fuel())->assertNotFound();
        $this->deleteJson($path.'/1')->assertNotFound();
        $this->getJson('/api/motocicletas/'.$moto->id.'/manutencoes')->assertOk();
        $this->getJson('/api/motocicletas/'.$moto->id.'/alertas')->assertOk();
    }
}
