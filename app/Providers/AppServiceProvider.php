<?php

namespace App\Providers;

use App\Services\ApiTokenService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ApiTokenService::class, function ($app): ApiTokenService {
            $config = $app['config'];

            return new ApiTokenService(
                secret: (string) $config->get('jwt.secret', ''),
                ttl: (int) $config->get('jwt.ttl', 3600),
                issuer: $config->get('jwt.issuer'),
            );
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
