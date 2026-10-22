<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\WebPushConfiguration;
use App\Services\WebPushService;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Minishlink\WebPush\WebPush;
use RuntimeException;
use Tests\TestCase;

class WebPushTest extends TestCase
{
    use RefreshDatabase, \Tests\Support\BrowserSubscription;

    private string $keyPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keyPath = storage_path('framework/testing/vapid-'.bin2hex(random_bytes(8)).'.json');
        config(['webpush.key_path' => $this->keyPath]);
    }

    protected function tearDown(): void
    {
        if (is_file($this->keyPath)) {
            unlink($this->keyPath);
        }
        parent::tearDown();
    }

    private function pilot(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['password_hash_web' => $user->password]);

        return $user;
    }

    private function transport(array $responses, array &$history): void
    {
        app(WebPushConfiguration::class)->initialize();
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($history));
        $push = new WebPush(['VAPID' => app(WebPushConfiguration::class)->keys()], ['TTL' => 3600, 'urgency' => 'normal'], 20, ['handler' => $handler, 'allow_redirects' => false]);
        $this->app->instance(WebPush::class, $push);
    }

    public function test_local_key_generation_is_idempotent_and_only_public_key_is_exposed(): void
    {
        $this->pilot();
        $this->getJson('/api/push-config')->assertJsonPath('configured', false);
        $this->artisan('push:configurar')->assertSuccessful();
        $first = file_get_contents($this->keyPath);
        $this->artisan('push:configurar')->assertSuccessful();
        $this->assertSame($first, file_get_contents($this->keyPath));
        $response = $this->getJson('/api/push-config')->assertOk()->assertJsonPath('configured', true)->assertJsonPath('subscriptionRegistered', false)->assertJsonMissingPath('privateKey');
        $this->assertSame(json_decode($first, true)['publicKey'], $response->json('publicKey'));
        $this->assertStringNotContainsString(json_decode($first, true)['privateKey'], $response->getContent());
    }

    public function test_subscription_is_encrypted_hidden_moved_between_accounts_and_cleared_at_logout(): void
    {
        $first = $this->pilot();
        $data = $this->browserSubscription();
        $this->putJson('/api/push-subscription', $data)->assertOk();
        $this->assertSame($data, $first->fresh()->push_subscription);
        $raw = DB::table('usuarios')->where('id', $first->id)->value('push_subscription');
        $this->assertStringNotContainsString($data['endpoint'], $raw);
        $this->getJson('/api/user')->assertJsonMissingPath('push_subscription')->assertJsonMissingPath('push_endpoint_hash')->assertJsonMissingPath('fcm_token')->assertJsonMissingPath('password');
        $second = $this->pilot();
        $this->putJson('/api/push-subscription', $data)->assertOk();
        $this->assertNull($first->fresh()->push_subscription);
        $this->assertSame($data, $second->fresh()->push_subscription);
        $this->getJson('/api/push-config')->assertJsonPath('subscriptionRegistered', true);
        $this->postJson('/api/logout')->assertOk();
        $this->assertNull($second->fresh()->push_subscription);
        $this->assertNull($second->fresh()->push_endpoint_hash);
        $this->putJson('/api/push-subscription', $data)->assertUnauthorized();
    }

    public function test_unsubscribe_cannot_remove_another_browser_or_account(): void
    {
        $first = $this->pilot();
        $a = $this->browserSubscription('a');
        $this->putJson('/api/push-subscription', $a)->assertOk();
        $second = $this->pilot();
        $b = $this->browserSubscription('b');
        $this->putJson('/api/push-subscription', $b)->assertOk();
        $this->deleteJson('/api/push-subscription', ['endpoint' => $a['endpoint']])->assertNoContent();
        $this->assertSame($a, $first->fresh()->push_subscription);
        $this->assertSame($b, $second->fresh()->push_subscription);
        $this->deleteJson('/api/push-subscription', ['endpoint' => $b['endpoint']])->assertNoContent();
        $this->assertNull($second->fresh()->push_subscription);
    }

    public function test_invalid_keys_and_unsafe_endpoints_are_rejected(): void
    {
        $this->pilot();
        $data = $this->browserSubscription();
        foreach (['http://fcm.googleapis.com/push', 'https://127.0.0.1/push', 'https://localhost/push', 'https://example.com/push', 'https://fcm.googleapis.com.evil.test/push', 'https://user:pass@fcm.googleapis.com/push', 'https://fcm.googleapis.com:8443/push', 'https://fcm.googleapis.com/push#fragment'] as $endpoint) {
            $this->putJson('/api/push-subscription', [...$data, 'endpoint' => $endpoint])->assertUnprocessable();
        }
        $this->putJson('/api/push-subscription', [...$data, 'keys' => ['p256dh' => 'invalid', 'auth' => 'invalid']])->assertUnprocessable();
        $this->assertNull(auth()->user()->fresh()->push_subscription);
    }

    public function test_test_notification_requires_the_current_subscription_and_handles_failures(): void
    {
        $user = $this->pilot();
        $data = $this->browserSubscription();
        $this->putJson('/api/push-subscription', $data)->assertOk();
        $this->postJson('/api/push-test', ['endpoint' => $data['endpoint']])->assertStatus(503);
        app(WebPushConfiguration::class)->initialize();
        $this->postJson('/api/push-test', ['endpoint' => $data['endpoint'].'-other'])->assertUnprocessable();
        $this->mock(WebPushService::class, fn ($mock) => $mock->shouldReceive('sendTest')->once()->withArgs(fn ($received) => $received->id === $user->id));
        $this->postJson('/api/push-test', ['endpoint' => $data['endpoint']])->assertStatus(202);
        $this->postJson('/api/push-test', ['endpoint' => $data['endpoint']])
            ->assertStatus(429)->assertHeader('Retry-After');
        // Limite de três testes/minuto: testar falha em uma janela seguinte.
        $this->travel(2)->minutes();
        $this->mock(WebPushService::class, fn ($mock) => $mock->shouldReceive('sendTest')->once()->andThrow(new RuntimeException('private details')));
        $response = $this->postJson('/api/push-test', ['endpoint' => $data['endpoint']])->assertStatus(503);
        $this->assertStringNotContainsString('private details', $response->getContent());
        $this->travelBack();
    }

    public function test_transport_encrypts_payload_and_signs_vapid_without_network(): void
    {
        $user = $this->pilot();
        $data = $this->browserSubscription();
        $this->putJson('/api/push-subscription', $data)->assertOk();
        $history = [];
        $this->transport([new Response(201)], $history);
        app(WebPushService::class)->sendTest($user->fresh());
        $this->assertCount(1, $history);
        $request = $history[0]['request'];
        $this->assertSame($data['endpoint'], (string) $request->getUri());
        $this->assertSame('aes128gcm', $request->getHeaderLine('Content-Encoding'));
        $this->assertStringStartsWith('vapid ', $request->getHeaderLine('Authorization'));
        $this->assertStringNotContainsString('MotoCare', (string) $request->getBody());
        $this->assertGreaterThan(100, strlen((string) $request->getBody()));
        $this->assertSame($data, $user->fresh()->push_subscription);
    }

    public function test_expired_subscription_is_removed_and_alert_remains_pending(): void
    {
        $user = $this->pilot();
        $data = $this->browserSubscription();
        $this->putJson('/api/push-subscription', $data)->assertOk();
        $moto = $user->motocicletas()->create(['marca' => 'Honda', 'modelo' => 'CG', 'ano' => 2025, 'placa' => 'EXP1D23', 'km_atual' => 900]);
        $alert = $moto->alertas()->create(['tipo_alerta' => 'troca_oleo', 'km_limite' => 1000, 'status' => 'pendente', 'nivel_urgencia' => 'media']);
        $history = [];
        $this->transport([new Response(410)], $history);
        $this->artisan('alertas:verificar')->assertFailed();
        $this->assertSame('pendente', $alert->fresh()->status);
        $this->assertNull($user->fresh()->push_subscription);
        $this->assertNull($user->fresh()->push_endpoint_hash);
    }

    public function test_transient_failure_keeps_subscription_and_redirects_are_not_followed(): void
    {
        $user = $this->pilot();
        $data = $this->browserSubscription();
        $this->putJson('/api/push-subscription', $data)->assertOk();
        $history = [];
        $this->transport([new Response(503), new Response(302, ['Location' => 'https://127.0.0.1/private'])], $history);
        for ($i = 0; $i < 2; $i++) {
            $failed = false;
            try {
                app(WebPushService::class)->sendTest($user->fresh());
            } catch (RuntimeException) {
                $failed = true;
            }
            $this->assertTrue($failed, 'O envio deveria falhar.');
            $this->assertSame($data, $user->fresh()->push_subscription);
        }
        $this->assertCount(2, $history);
        $this->assertSame($data['endpoint'], (string) $history[1]['request']->getUri());
    }
}
