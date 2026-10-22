<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_passwords_do_not_silently_truncate_or_crash_the_hasher(): void
    {
        foreach ([str_repeat('a', 73), str_repeat('é', 37), "password\0with-null"] as $password) {
            $this->postJson('/api/register', ['nome' => 'Piloto', 'email' => 'safe@example.test', 'password' => $password, 'password_confirmation' => $password])
                ->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $this->assertDatabaseCount('usuarios', 0);
        $password = str_repeat('é', 36);
        $this->postJson('/api/register', ['nome' => 'Piloto', 'email' => 'safe@example.test', 'password' => $password, 'password_confirmation' => $password])
            ->assertCreated()->assertJsonMissingPath('password');
        $this->putJson('/api/password', ['current_password' => $password, 'password' => str_repeat('b', 73), 'password_confirmation' => str_repeat('b', 73)])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_api_errors_hide_internal_details_even_when_debug_is_enabled(): void
    {
        config(['app.debug' => true]);
        Route::get('/api/isolated-security-error', fn () => throw new \RuntimeException('SENSITIVE_TEST_MARKER'));
        $response = $this->getJson('/api/isolated-security-error')->assertStatus(500);
        foreach (['SENSITIVE_TEST_MARKER', 'trace', 'exception', 'file', 'line'] as $private) {
            $this->assertStringNotContainsString($private, $response->getContent());
        }
        $response->assertHeader('Cache-Control', 'no-store, private')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->getJson('/api/isolated-missing-route')->assertNotFound()->assertJsonMissingPath('trace')->assertJsonMissingPath('exception');
        $this->postJson('/api/isolated-security-error')->assertStatus(405)->assertJsonMissingPath('trace')->assertJsonMissingPath('exception');
    }

    public function test_unauthenticated_api_mutations_require_csrf_outside_the_test_bypass(): void
    {
        $this->app->instance('env', 'local');
        config(['app.debug' => true]);
        $this->postJson('/api/register', ['nome' => 'Piloto', 'email' => 'safe@example.test', 'password' => 'password123', 'password_confirmation' => 'password123'])
            ->assertStatus(419)->assertJsonMissingPath('trace')->assertJsonMissingPath('exception');
        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_motorcycle_plate_is_normalized_and_duplicate_creation_is_rejected(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $payload = ['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2026, 'placa' => ' abc-1d23 ', 'km_atual' => 0];
        $id = $this->postJson('/api/motocicletas', $payload)->assertCreated()->assertJsonPath('placa', 'ABC1D23')->json('id');
        $this->postJson('/api/motocicletas', [...$payload, 'placa' => 'ABC1D23'])->assertUnprocessable();
        $this->putJson('/api/motocicletas/'.$id, [...$payload, 'placa' => 'def 2g34'])->assertOk()->assertJsonPath('placa', 'DEF2G34');
    }

    public function test_malformed_password_checks_are_validation_errors_and_keep_the_account_unchanged(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $hash = $owner->password;
        $this->putJson('/api/password', ['current_password' => ['invalid'], 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'])
            ->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->putJson('/api/user', ['nome' => $owner->nome, 'email' => 'other@example.test', 'current_password' => ['invalid']])
            ->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->assertSame($hash, $owner->fresh()->password);
        $this->assertSame($owner->email, $owner->fresh()->email);
    }

    public function test_password_guessing_through_an_authenticated_session_is_rate_limited(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        config(['app.debug' => true]);
        $payload = ['current_password' => 'wrong-password', 'password' => 'newPassword123', 'password_confirmation' => 'newPassword123'];
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->putJson('/api/password', $payload)->assertUnprocessable();
        }
        $this->putJson('/api/password', $payload)->assertStatus(429)->assertHeader('Retry-After')->assertJsonMissingPath('trace');
    }

    public function test_padded_alert_identifiers_cannot_bypass_the_scheduler_lock(): void
    {
        $owner = User::factory()->create();
        $moto = $owner->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2026, 'placa' => 'LCK1D23', 'km_atual' => 1000]);
        $alert = $moto->alertas()->create(['tipo_alerta' => 'revisao', 'km_base' => 1000, 'km_limite' => 2000, 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $lock = new class
        {
            public function block($seconds, $callback)
            {
                return $callback();
            }
        };
        Cache::shouldReceive('lock')->twice()->with('alerta.send.'.$alert->id, 120)->andReturn($lock);
        $this->actingAs($owner);
        $path = '/api/motocicletas/'.$moto->id.'/alertas/000'.$alert->id;
        $this->putJson($path, ['status' => 'concluido'])->assertOk()->assertJsonPath('status', 'concluido');
        $this->deleteJson($path)->assertNoContent();
    }
}
