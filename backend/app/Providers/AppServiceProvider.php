<?php

namespace App\Providers;

use App\Services\WebPushConfiguration;
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
        //
    }
}
