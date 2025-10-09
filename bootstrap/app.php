<?php

use App\Http\Middleware\AuthenticateApiRequest;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
        //
    })->create();
