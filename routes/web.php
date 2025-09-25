<?php

use App\Http\Controllers\ContactController;
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

// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
// Route::resource('inmuebles', InmuebleController::class);
// Route::resource('arrendadores', ArrendadorController::class);
// Route::resource('inquilinos', InquilinoController::class);
// Route::resource('polizas', PolizaController::class);
// Route::resource('blog', BlogController::class);
// Route::get('/finanzas', [FinanzaController::class, 'index'])->name('finanzas.index');


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
});

require __DIR__ . '/auth.php';
