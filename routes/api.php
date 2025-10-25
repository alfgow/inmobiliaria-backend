<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\ContactCommentController;
use App\Http\Controllers\Api\ContactController as ApiContactController;
use App\Http\Controllers\Api\ContactIaInteractionController;
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
            Route::get('inmuebles/search-by-slug/{slug}', [InmuebleController::class, 'searchBySlug'])
                ->name('inmuebles.search-by-slug');
            Route::get('inmuebles/{inmueble}', [InmuebleController::class, 'show'])
                ->name('inmuebles.show');

            Route::get('contactos', [ApiContactController::class, 'index'])
                ->name('contactos.index');
            Route::post('contactos', [ApiContactController::class, 'store'])
                ->name('contactos.store');
            Route::get('contactos/{contact}', [ApiContactController::class, 'show'])
                ->name('contactos.show');
            Route::match(['put', 'patch'], 'contactos/{contact}', [ApiContactController::class, 'update'])
                ->name('contactos.update');

            Route::get('contactos/{contact}/comentarios', [ContactCommentController::class, 'index'])
                ->name('contactos.comentarios.index');
            Route::post('contactos/{contact}/comentarios', [ContactCommentController::class, 'store'])
                ->name('contactos.comentarios.store');
            Route::match(['put', 'patch'], 'contactos/{contact}/comentarios/{comentario}', [ContactCommentController::class, 'update'])
                ->name('contactos.comentarios.update');

            Route::get('contactos/{contact}/interacciones-ia', [ContactIaInteractionController::class, 'index'])
                ->name('contactos.interacciones-ia.index');
            Route::post('contactos/{contact}/interacciones-ia', [ContactIaInteractionController::class, 'store'])
                ->name('contactos.interacciones-ia.store');
            Route::match(['put', 'patch'], 'contactos/{contact}/interacciones-ia/{interaccion}', [ContactIaInteractionController::class, 'update'])
                ->name('contactos.interacciones-ia.update');
        });
    });
