<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\FormationCategoryController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\FormationProcessController;
use App\Http\Controllers\TurnoverController;
use App\Http\Controllers\DriverConcernController;
use App\Http\Controllers\ConcernTypeController;
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
    Route::get('/drivers/alerts', [DriversController::class, 'alerts'])->name('drivers.alerts');
    Route::get('/drivers/alerts/export', [DriversController::class, 'exportAlerts'])->name('drivers.alerts.export');
    Route::get('/drivers/{driver}', [DriversController::class, 'show'])->name('drivers.show');
    Route::get('/drivers/{driver}/edit', [DriversController::class, 'edit'])->name('drivers.edit');
    Route::patch('/drivers/{driver}', [DriversController::class, 'update'])->name('drivers.update');
    Route::post('/drivers/{driver}/documents', [DriversController::class, 'uploadDocuments'])->name('drivers.upload-documents');
    Route::delete('/drivers/{driver}/documents/{index}', [DriversController::class, 'deleteDocument'])->name('drivers.delete-document');
    Route::post('/drivers/{driver}/formations/quick-store', [DriversController::class, 'storeQuickFormation'])->name('drivers.formations.quick-store');
    Route::get('/driver-formations/{driverFormation}/certificate', [DriversController::class, 'downloadFormationCertificate'])->name('drivers.formations.download-certificate');
    Route::post('/drivers/{driver}/activities', [DriversController::class, 'storeActivity'])->name('drivers.activities.store');
    Route::get('/drivers/{driver}/activities/export-pdf', [DriversController::class, 'exportTimelinePDF'])->name('drivers.activities.export-pdf');
    Route::get('/drivers/{driver}/activities/export-csv', [DriversController::class, 'exportTimelineCSV'])->name('drivers.activities.export-csv');

    // Formation Types & Categories
    Route::resource('formations', FormationController::class);
    Route::resource('formation-categories', FormationCategoryController::class)->except(['show']);
    
    // Concerns Types & Driver Concerns
    Route::resource('concern-types', ConcernTypeController::class)
        ->except(['show', 'create', 'edit'])
        ->names([
            'index' => 'concerns.concern-types.index',
            'store' => 'concerns.concern-types.store',
            'update' => 'concerns.concern-types.update',
            'destroy' => 'concerns.concern-types.destroy',
        ]);

    Route::resource('driver-concerns', DriverConcernController::class)
        ->names([
            'index' => 'concerns.driver-concerns.index',
            'create' => 'concerns.driver-concerns.create',
            'store' => 'concerns.driver-concerns.store',
            'show' => 'concerns.driver-concerns.show',
            'edit' => 'concerns.driver-concerns.edit',
            'update' => 'concerns.driver-concerns.update',
            'destroy' => 'concerns.driver-concerns.destroy',
        ]);

    Route::post('driver-concerns/{driver_concern}/complete', [DriverConcernController::class, 'complete'])
        ->name('concerns.driver-concerns.complete');

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

    // Turnovers
    Route::resource('turnovers', TurnoverController::class);
    Route::post('/turnovers/{turnover}/confirm', [TurnoverController::class, 'confirm'])->name('turnovers.confirm');
    Route::get('/turnovers/{turnover}/interview', [TurnoverController::class, 'showInterviewForm'])->name('turnovers.interview');
    Route::post('/turnovers/{turnover}/interview', [TurnoverController::class, 'storeInterviewAnswers'])->name('turnovers.interview.store');
    Route::get('/turnovers/{turnover}/interview/download', [TurnoverController::class, 'downloadInterviewPdf'])->name('turnovers.interview.download');

    // Formation Processes
    Route::get('/formation-processes', [FormationProcessController::class, 'index'])->name('formation-processes.index');
    Route::get('/formation-processes/create', [FormationProcessController::class, 'create'])->name('formation-processes.create');
    Route::post('/formation-processes', [FormationProcessController::class, 'store'])->name('formation-processes.store');
    Route::get('/formation-processes/{formationProcess}', [FormationProcessController::class, 'show'])->name('formation-processes.show');
    Route::get('/formation-processes/{formationProcess}/step/{stepNumber}', [FormationProcessController::class, 'step'])->name('formation-processes.step');
    Route::post('/formation-processes/{formationProcess}/step/{stepNumber}', [FormationProcessController::class, 'saveStep'])->name('formation-processes.save-step');
    Route::post('/formation-processes/{formationProcess}/step/{stepNumber}/validate', [FormationProcessController::class, 'validateStep'])->name('formation-processes.validate-step');
    Route::post('/formation-processes/{formationProcess}/step/{stepNumber}/reject', [FormationProcessController::class, 'rejectStep'])->name('formation-processes.reject-step');
    Route::post('/formation-processes/{formationProcess}/finalize', [FormationProcessController::class, 'finalize'])->name('formation-processes.finalize');
    Route::get('/formation-processes/{formationProcess}/report', [FormationProcessController::class, 'downloadReport'])->name('formation-processes.download-report');
    
});

require __DIR__.'/auth.php';
