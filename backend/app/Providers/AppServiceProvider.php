<?php

namespace App\Providers;

use App\Services\WebPushConfiguration;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Minishlink\WebPush\WebPush;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WebPush::class, function ($app) {
            $keys = $app->make(WebPushConfiguration::class)->keys();

            return new WebPush(['VAPID' => $keys], ['TTL' => 3600, 'urgency' => 'normal'], 20, ['allow_redirects' => false, 'connect_timeout' => 5]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (['send' => [5, 3], 'reset' => [10, 10]] as $action => [$perMinute, $perHour]) {
            RateLimiter::for('password-recovery-'.$action, function (Request $request) use ($action, $perMinute, $perHour) {
                $email = $request->input('email');
                $emailKey = hash('sha256', is_string($email) ? mb_strtolower(trim($email)) : 'invalid');

                return [
                    Limit::perMinute($perMinute)->by($action.':ip:'.$request->ip()),
                    Limit::perHour($perHour)->by($action.':email:'.$emailKey),
                ];
            });
        }
    }
}
