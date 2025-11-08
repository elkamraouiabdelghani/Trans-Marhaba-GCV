<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\FormationTypeController;
use App\Http\Controllers\IntegrationController;
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
    Route::get('/drivers/{driver}', [DriversController::class, 'show'])->name('drivers.show');
    Route::get('/drivers/{driver}/edit', [DriversController::class, 'edit'])->name('drivers.edit');
    Route::patch('/drivers/{driver}', [DriversController::class, 'update'])->name('drivers.update');

    // Formation Types
    Route::resource('formation-types', FormationTypeController::class);

    // Integrations
    Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/create', [IntegrationController::class, 'create'])->name('integrations.create');
    Route::post('/integrations', [IntegrationController::class, 'store'])->name('integrations.store');
    Route::get('/integrations/{integration}', [IntegrationController::class, 'show'])->name('integrations.show');
    Route::get('/integrations/{integration}/step/{stepNumber}', [IntegrationController::class, 'step'])->name('integrations.step');
    Route::post('/integrations/{integration}/step/{stepNumber}', [IntegrationController::class, 'saveStep'])->name('integrations.save-step');
    
    Route::post('/integrations/{integration}/step/{stepNumber}/validate', [IntegrationController::class, 'validateStep'])->name('integrations.validate-step');
    Route::post('/integrations/{integration}/step/{stepNumber}/reject', [IntegrationController::class, 'rejectStep'])->name('integrations.reject-step');
    Route::post('/integrations/{integration}/finalize', [IntegrationController::class, 'finalize'])->name('integrations.finalize');
    
});

require __DIR__.'/auth.php';
