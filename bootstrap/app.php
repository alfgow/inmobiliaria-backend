<?php

use App\Http\Middleware\AuthenticateApiRequest;
use App\Services\ErrorLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function (): void {
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by(
                    $request->user()?->getAuthIdentifier() ?? $request->ip()
                );
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.api' => AuthenticateApiRequest::class,
            'bindings' => SubstituteBindings::class,
            'throttle' => ThrottleRequests::class,
        ]);

        $middleware->group('api', [
            HandleCors::class,
            'throttle:api',
            'bindings',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $exception) {
            app(ErrorLogger::class)->log($exception);
        });
    })->create();
