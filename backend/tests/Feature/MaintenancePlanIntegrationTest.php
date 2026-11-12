<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenancePlanIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_and_retrieve_maintenance_for_owned_motorcycle(): void
    {
        $user = User::factory()->create();
        $motorcycle = $user->motocicletas()->create([
            'marca' => 'Honda',
            'modelo' => 'CG 160',
            'ano' => 2026,
            'placa' => 'TST1A23',
            'km_atual' => 1200,
        ]);

        $this->actingAs($user);
        $path = '/api/motocicletas/'.$motorcycle->id.'/manutencoes';
        $payload = [
            'tipo_servico' => 'troca_oleo',
            'descricao' => 'Troca de óleo',
            'valor' => '125.90',
            'status' => 'concluido',
            'data' => '2026-11-10',
            'km_servico' => 1200,
        ];

        $created = $this->postJson($path, $payload)
            ->assertCreated()
            ->assertJsonPath('valor', '125.90')
            ->assertJsonPath('status', 'concluido');

        $this->assertDatabaseHas('manutencoes', [
            'id' => $created->json('id'),
            'motocicleta_id' => $motorcycle->id,
            'tipo_servico' => 'troca_oleo',
        ]);

        $this->getJson($path)
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $created->json('id'));
    }
}
