<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PasswordRecovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['password_recovery.frontend_url' => 'https://motocare.example.test']);
    }

    private function credentials(User $user, ?string $token = null, string $password = 'NewPassword123'): array
    {
        return ['email' => $user->email, 'token' => $token ?? Password::broker()->createToken($user), 'password' => $password, 'password_confirmation' => $password];
    }

    private function messages()
    {
        return Mail::mailer()->getSymfonyTransport()->messages();
    }

    public function test_link_is_mailed_once_with_a_hashed_token_and_trusted_frontend_origin(): void
    {
        $user = User::factory()->create(['email' => 'Pilot+Moto@example.test']);
        $response = $this->withHeaders(['Origin' => 'https://attacker.example.test', 'X-Forwarded-Host' => 'attacker.example.test'])
            ->postJson('/api/forgot-password', ['email' => 'pilot+moto@example.test'])
            ->assertStatus(202)->assertHeader('Cache-Control', 'no-store, private')->assertJsonMissingPath('token');
        $this->assertCount(1, $this->messages());
        $message = $this->messages()->first()->getOriginalMessage();
        $this->assertSame($user->email, $message->getTo()[0]->getAddress());
        preg_match('~https://motocare\.example\.test/redefinir-senha#([^\s]+)~', $message->getTextBody(), $match);
        parse_str($match[1], $link);
        $this->assertSame($user->email, $link['email']);
        $this->assertStringNotContainsString('attacker.example.test', $message->getTextBody());
        $stored = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');
        $this->assertNotSame($stored, $link['token']);
        $this->assertTrue(Hash::check($link['token'], $stored));
        $this->assertStringNotContainsString($link['token'], $response->getContent());
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(202);
        $this->assertCount(1, $this->messages());
        $this->assertTrue(Password::broker()->tokenExists($user, $link['token']));
    }

    public function test_unknown_accounts_receive_the_same_response_without_email_or_tokens(): void
    {
        $user = User::factory()->create();
        $known = $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(202)->json();
        $this->postJson('/api/forgot-password', ['email' => 'missing@example.test'])->assertStatus(202)->assertExactJson($known);
        $this->assertCount(1, $this->messages());
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    public function test_reset_changes_the_password_once_without_logging_in_or_changing_motorcycles(): void
    {
        $user = User::factory()->create();
        $moto = $user->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2026, 'placa' => 'PWD1D23', 'km_atual' => 1234]);
        $before = $moto->fresh()->getAttributes();
        $data = $this->credentials($user);
        $this->postJson('/api/reset-password', $data)->assertOk()->assertJsonMissingPath('token')->assertJsonMissingPath('password');
        $this->assertTrue(Hash::check($data['password'], $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertSame($before, $moto->fresh()->getAttributes());
        $this->getJson('/api/user')->assertUnauthorized();
        $this->postJson('/api/reset-password', [...$data, 'password' => 'AnotherPassword123', 'password_confirmation' => 'AnotherPassword123'])
            ->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])->assertUnprocessable();
        $this->postJson('/api/login', ['email' => $user->email, 'password' => $data['password']])->assertOk();
    }

    public function test_expired_wrong_and_foreign_tokens_cannot_change_passwords(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $data = $this->credentials($user);
        $first = $this->postJson('/api/reset-password', [...$data, 'token' => str_repeat('0', 64)])->assertUnprocessable()->json('errors');
        $this->postJson('/api/reset-password', [...$data, 'email' => $other->email])->assertUnprocessable()->assertJsonPath('errors', $first);
        $this->postJson('/api/reset-password', [...$data, 'email' => 'missing@example.test'])->assertUnprocessable()->assertJsonPath('errors', $first);
        DB::table('password_reset_tokens')->update(['created_at' => now()->subMinutes(61)]);
        $this->postJson('/api/reset-password', $data)->assertUnprocessable()->assertJsonPath('errors', $first);
        $this->assertSame($user->password, $user->fresh()->password);
        $this->assertSame($other->password, $other->fresh()->password);
    }

    public function test_new_link_invalidates_the_previous_link_and_preserves_password_until_used(): void
    {
        $user = User::factory()->create();
        $old = $this->credentials($user);
        DB::table('password_reset_tokens')->update(['created_at' => now()->subMinutes(2)]);
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(202);
        $this->assertFalse(Password::broker()->tokenExists($user, $old['token']));
        $this->assertSame($user->password, $user->fresh()->password);
        $this->postJson('/api/reset-password', $old)->assertUnprocessable();
    }

    public function test_password_rules_and_confirmation_do_not_consume_a_valid_link(): void
    {
        $user = User::factory()->create();
        $data = $this->credentials($user);
        foreach (['short', str_repeat('a', 73), str_repeat('é', 37), "password\0null"] as $password) {
            $this->postJson('/api/reset-password', [...$data, 'password' => $password, 'password_confirmation' => $password])->assertUnprocessable()->assertJsonValidationErrors('password');
        }
        $this->postJson('/api/reset-password', [...$data, 'password_confirmation' => 'different'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertTrue(Password::broker()->tokenExists($user, $data['token']));
        $this->postJson('/api/reset-password', $data)->assertOk();
    }

    public function test_reset_revokes_only_target_sessions_and_push_and_keeps_other_accounts(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create(['push_subscription' => ['endpoint' => 'https://push.example.test/private'], 'push_endpoint_hash' => str_repeat('a', 64)]);
        $other = User::factory()->create();
        foreach (['target-one' => $user, 'target-two' => $user, 'other' => $other] as $id => $owner) {
            DB::table('sessions')->insert(['id' => $id, 'user_id' => $owner->id, 'payload' => '', 'last_activity' => time()]);
        }
        $this->postJson('/api/reset-password', $this->credentials($user))->assertOk();
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseHas('sessions', ['id' => 'other', 'user_id' => $other->id]);
        $this->assertNull($user->fresh()->push_subscription);
        $this->assertNull($user->fresh()->push_endpoint_hash);
        $this->assertSame($other->password, $other->fresh()->password);
    }

    public function test_current_session_of_target_user_is_logged_out_after_reset(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['password_hash_web' => $user->password]);
        $this->postJson('/api/reset-password', $this->credentials($user))->assertOk();
        $this->assertGuest();
        $this->getJson('/api/user')->assertUnauthorized();
    }

    public function test_recovery_requires_csrf_even_without_authentication(): void
    {
        $this->app->instance('env', 'local');
        $this->postJson('/api/forgot-password', ['email' => 'pilot@example.test'])->assertStatus(419);
        $this->postJson('/api/reset-password', [])->assertStatus(419);
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_rate_limits_are_independent_of_account_existence_and_email_case(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/forgot-password', ['email' => 'missing@example.test'])->assertStatus(202);
        }
        $this->postJson('/api/forgot-password', ['email' => 'MISSING@example.test'])->assertStatus(429)->assertHeader('Retry-After');
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/reset-password', [])->assertUnprocessable();
        }
        $this->postJson('/api/reset-password', [])->assertStatus(429)->assertHeader('Retry-After');
    }

    public function test_log_mailer_and_unsafe_frontend_configuration_are_refused_without_creating_tokens(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $user = User::factory()->create();
        Log::shouldReceive('debug')->never();
        config(['mail.default' => 'log']);
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(503);
        $this->postJson('/api/forgot-password', ['email' => 'missing@example.test'])->assertStatus(503);
        config(['mail.default' => 'array']);
        foreach (['http://public.example.test', 'https://user:password@motocare.example.test', 'https://motocare.example.test?redirect=unsafe', 'https://motocare.example.test/#fragment', 'invalid'] as $url) {
            config(['password_recovery.frontend_url' => $url]);
            $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(503);
        }
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_transport_failure_is_private_and_does_not_leave_a_usable_token(): void
    {
        $user = User::factory()->create();
        Mail::shouldReceive('mailer')->once()->andReturnSelf();
        Mail::shouldReceive('raw')->once()->andThrow(new \RuntimeException('PRIVATE_SMTP_CREDENTIAL_AND_TOKEN'));
        Log::shouldReceive('warning')->once()->with('Falha no envio de recuperação de senha. Confira o serviço SMTP.');
        $this->postJson('/api/forgot-password', ['email' => $user->email])->assertStatus(202)->assertJsonMissingPath('token');
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->assertSame($user->password, $user->fresh()->password);
    }

    public function test_authenticated_password_change_invalidates_pending_recovery_links(): void
    {
        $user = User::factory()->create();
        $data = $this->credentials($user);
        $this->actingAs($user)->withSession(['password_hash_web' => $user->password]);
        $this->putJson('/api/password', ['current_password' => 'password', 'password' => 'ChangedInSettings123', 'password_confirmation' => 'ChangedInSettings123'])->assertOk();
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->postJson('/api/reset-password', $data)->assertUnprocessable();
        $this->assertTrue(Hash::check('ChangedInSettings123', $user->fresh()->password));
    }

    public function test_profile_change_keeps_links_until_email_changes_and_old_address_reuse_is_safe(): void
    {
        $user = User::factory()->create();
        $data = $this->credentials($user);
        $this->actingAs($user)->withSession(['password_hash_web' => $user->password]);
        $this->putJson('/api/user', ['nome' => 'Novo nome', 'email' => $user->email])->assertOk();
        $this->assertTrue(Password::broker()->tokenExists($user, $data['token']));
        $this->putJson('/api/user', ['nome' => 'Novo nome', 'email' => 'changed@example.test', 'current_password' => 'password'])->assertOk();
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->postJson('/api/register', ['nome' => 'Outro piloto', 'email' => $data['email'], 'password' => 'OtherPassword123', 'password_confirmation' => 'OtherPassword123'])->assertCreated();
        $this->postJson('/api/reset-password', $data)->assertUnprocessable();
        $this->assertTrue(Hash::check('OtherPassword123', User::where('email', $data['email'])->firstOrFail()->password));
    }

    public function test_registration_clears_orphaned_recovery_links_for_reused_email(): void
    {
        $old = User::factory()->create();
        $data = $this->credentials($old);
        $old->delete();
        $this->assertDatabaseCount('password_reset_tokens', 1);
        $this->postJson('/api/register', ['nome' => 'Novo piloto', 'email' => $data['email'], 'password' => 'AnotherPassword123', 'password_confirmation' => 'AnotherPassword123'])->assertCreated();
        $this->assertDatabaseCount('password_reset_tokens', 0);
        $this->postJson('/api/reset-password', $data)->assertUnprocessable();
    }

    public function test_delivery_requires_smtp_and_a_sender_and_never_enables_array_outside_tests(): void
    {
        config(['mail.default' => 'smtp', 'mail.from.address' => 'hello@example.com']);
        $this->assertFalse(app(PasswordRecovery::class)->deliveryAvailable());
        config(['mail.from.address' => 'noreply@motocare.example.test']);
        $this->assertTrue(app(PasswordRecovery::class)->deliveryAvailable());
        config(['mail.default' => 'array']);
        $this->app->instance('env', 'local');
        $this->assertFalse(app(PasswordRecovery::class)->deliveryAvailable());
    }
}
