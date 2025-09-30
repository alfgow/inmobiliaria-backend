<?php

use App\Http\Controllers\CodigoPostalController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\InmuebleController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('/contactos', [ContactController::class, 'index'])
        ->name('contactos.index');
    Route::get('/contactos/nuevo', [ContactController::class, 'create'])
        ->name('contactos.create');
    Route::post('/contactos', [ContactController::class, 'store'])
        ->name('contactos.store');
    Route::get('/contactos/{contact}', [ContactController::class, 'show'])
        ->name('contactos.show');
    Route::delete('/contactos/{contact}', [ContactController::class, 'destroy'])
        ->name('contactos.destroy');
    Route::post('/contactos/{contact}/comentarios', [ContactController::class, 'storeComment'])
        ->name('contactos.comentarios.store');
    Route::post('/contactos/{contact}/intereses', [ContactController::class, 'storeInterest'])
        ->name('contactos.intereses.store');

    Route::get('/inmuebles/mapa', [InmuebleController::class, 'map'])
        ->name('inmuebles.map');
    Route::patch('/inmuebles/{inmueble}/destacado', [InmuebleController::class, 'updateDestacado'])
        ->name('inmuebles.destacado');
    Route::resource('inmuebles', InmuebleController::class)->except(['show']);

    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('codigos-postales', [CodigoPostalController::class, 'index'])
            ->name('codigos-postales.index');
        Route::get('codigos-postales/resolve', [CodigoPostalController::class, 'resolve'])
            ->name('codigos-postales.resolve');
    });

    Route::get('codigos-postales', [CodigoPostalController::class, 'index'])
        ->name('codigos-postales.index');
    Route::get('codigos-postales/resolve', [CodigoPostalController::class, 'resolve'])
        ->name('codigos-postales.resolve');
});

require __DIR__ . '/auth.php';
