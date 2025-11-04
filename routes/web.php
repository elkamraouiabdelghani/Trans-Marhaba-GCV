<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\DriverIntegrationController;
use App\Http\Controllers\FormationTypeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', function () {
    return view('auth/login');
});

// Locale switcher
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'fr'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Drivers
    Route::get('/drivers', [DriversController::class, 'index'])->name('drivers.index');
    
    // Driver Integration Wizard (must be before /drivers/{driver} to avoid route conflict)
    Route::get('/drivers/integrations', [DriverIntegrationController::class, 'index'])
        ->name('drivers.integrations');
    Route::post('/drivers/integrations/create', [DriverIntegrationController::class, 'create'])
        ->name('drivers.integrations.create');
    Route::get('/drivers/integrations/{integration}/step/{step}', [DriverIntegrationController::class, 'showStep'])
        ->name('drivers.integrations.step');
    Route::post('/drivers/integrations/{integration}/step/{step}', [DriverIntegrationController::class, 'saveStep'])
        ->name('drivers.integrations.step.save');
    
    // Drivers (specific routes must come after integrations routes)
    Route::get('/drivers/{driver}', [DriversController::class, 'show'])->name('drivers.show');

    // Formation Types
    Route::resource('formation-types', FormationTypeController::class);
});

require __DIR__.'/auth.php';
