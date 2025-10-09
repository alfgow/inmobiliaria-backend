<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\InmuebleController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')
    ->prefix('v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::post('auth/token', [AuthenticationController::class, 'store'])
            ->name('auth.token');

        Route::middleware('auth.api')->group(function (): void {
            Route::get('inmuebles', [InmuebleController::class, 'index'])
                ->name('inmuebles.index');
            Route::get('inmuebles/{inmueble}', [InmuebleController::class, 'show'])
                ->name('inmuebles.show');
        });
    });
