<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\AdministrationRoleController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\FormationProcessController;
use App\Http\Controllers\TurnoverController;
use App\Http\Controllers\DriverConcernController;
use App\Http\Controllers\OrganigramMemberController;
use App\Http\Controllers\ChangementTypeController;
use App\Http\Controllers\DriverHandoverController;
use App\Http\Controllers\PrincipaleCretaireController;
use App\Http\Controllers\SousCretaireController;
use App\Http\Controllers\ChangementController;
use App\Http\Controllers\CoachingCabineController;
use App\Http\Controllers\TbtFormationController;
use App\Http\Controllers\ViolationTypeController;
use App\Http\Controllers\DriverViolationController;
use App\Http\Controllers\ExportCenterController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

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
Route::get('/dashboard/calendar/pdf', [DashboardController::class, 'calendarPdf'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.calendar.pdf');

// Legacy route to serve files by path if ever needed; by default we use /storage URLs
// with the "public" disk (storage/app/public ↔ public/storage).

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Drivers
    Route::get('/drivers', [DriversController::class, 'index'])->name('drivers.index');
    Route::get('/drivers/export', [DriversController::class, 'export'])->name('drivers.export');
    Route::get('/drivers/terminated', [DriversController::class, 'terminated'])->name('drivers.terminated');
    Route::get('/drivers/activities', [DriversController::class, 'activitiesIndex'])->name('drivers.activities.index');
    Route::post('/drivers/activities/import', [DriversController::class, 'importActivities'])->name('drivers.activities.import');
    Route::get('/drivers/alerts', [DriversController::class, 'alerts'])->name('drivers.alerts');
    Route::get('/drivers/alerts/export', [DriversController::class, 'exportAlerts'])->name('drivers.alerts.export');
    Route::get('/drivers/{driver}', [DriversController::class, 'show'])->name('drivers.show');
    Route::get('/drivers/{driver}/edit', [DriversController::class, 'edit'])->name('drivers.edit');
    Route::patch('/drivers/{driver}', [DriversController::class, 'update'])->name('drivers.update');
    Route::post('/drivers/{driver}/documents', [DriversController::class, 'uploadDocuments'])->name('drivers.upload-documents');
    Route::delete('/drivers/{driver}/documents/{index}', [DriversController::class, 'deleteDocument'])->name('drivers.delete-document');
    Route::get('/drivers/{driver}/documents/{document}', [DriversController::class, 'showDocument'])->name('drivers.documents.show');
    Route::post('/drivers/{driver}/formations/quick-store', [DriversController::class, 'storeQuickFormation'])->name('drivers.formations.quick-store');
    Route::get('/driver-formations/{driverFormation}/certificate', [DriversController::class, 'downloadFormationCertificate'])->name('drivers.formations.download-certificate');
    Route::post('/drivers/{driver}/activities', [DriversController::class, 'storeActivity'])->name('drivers.activities.store');
    Route::get('/drivers/{driver}/activities/export-pdf', [DriversController::class, 'exportTimelinePDF'])->name('drivers.activities.export-pdf');
    Route::get('/drivers/{driver}/activities/export-csv', [DriversController::class, 'exportTimelineCSV'])->name('drivers.activities.export-csv');
    Route::get('/drivers/{driver}/violations/export-pdf', [DriversController::class, 'exportViolationsPDF'])->name('drivers.violations.export-pdf');
    Route::get('/drivers/{driver}/violations/export-csv', [DriversController::class, 'exportViolationsCSV'])->name('drivers.violations.export-csv');

    // Formation Types & Categories
    Route::get('/formations/planning', [FormationController::class, 'planning'])->name('formations.planning');
    Route::get('/formations/planning/pdf', [FormationController::class, 'planningPdf'])->name('formations.planning.pdf');
    Route::resource('formations', FormationController::class);
    Route::post('/formations/{formation}/mark-realized', [FormationController::class, 'markAsRealized'])->name('formations.mark-realized');

    // Changements
    Route::resource('changement-types', ChangementTypeController::class);
    Route::resource('principale-cretaires', PrincipaleCretaireController::class);
    Route::resource('sous-cretaires', SousCretaireController::class);
    
    // Changement Process
    Route::get('/changements', [ChangementController::class, 'index'])->name('changements.index');
    Route::get('/changements/create', [ChangementController::class, 'create'])->name('changements.create');
    Route::post('/changements', [ChangementController::class, 'store'])->name('changements.store');
    Route::get('/changements/{changement}', [ChangementController::class, 'show'])->name('changements.show');
    Route::get('/changements/{changement}/step/{stepNumber}', [ChangementController::class, 'step'])->name('changements.step');
    Route::post('/changements/{changement}/step/{stepNumber}', [ChangementController::class, 'saveStep'])->name('changements.save-step');
    Route::post('/changements/{changement}/step/{stepNumber}/validate', [ChangementController::class, 'validateStep'])->name('changements.validate-step');
    Route::post('/changements/{changement}/step/{stepNumber}/reject', [ChangementController::class, 'rejectStep'])->name('changements.reject-step');
    Route::get('/changements/{changement}/checklist', [ChangementController::class, 'checklist'])->name('changements.checklist');
    Route::post('/changements/{changement}/checklist', [ChangementController::class, 'saveChecklist'])->name('changements.save-checklist');
    Route::get('/changements/{changement}/checklist/download', [ChangementController::class, 'downloadChecklist'])->name('changements.checklist.download');
    Route::post('/changements/{changement}/finalize', [ChangementController::class, 'finalize'])->name('changements.finalize');
    
    // Driver Concerns
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

    // Violation Types
    Route::resource('violation-types', ViolationTypeController::class);

    // Driver Violations
    Route::resource('driver-violations', DriverViolationController::class)->names([
        'index' => 'violations.index',
        'create' => 'violations.create',
        'store' => 'violations.store',
        'show' => 'violations.show',
        'edit' => 'violations.edit',
        'update' => 'violations.update',
        'destroy' => 'violations.destroy',
    ]);
    Route::post('/driver-violations/{driverViolation}/mark-confirmed', [DriverViolationController::class, 'markAsConfirmed'])
        ->name('violations.mark-confirmed');
    Route::post('/driver-violations/{driverViolation}/mark-rejected', [DriverViolationController::class, 'markAsRejected'])
        ->name('violations.mark-rejected');
    Route::post('/driver-violations/{driverViolation}/update-status', [DriverViolationController::class, 'updateStatus'])
        ->name('violations.update-status');
    Route::get('/driver-violations/{driverViolation}/document', [DriverViolationController::class, 'downloadDocument'])
        ->name('violations.document');
    Route::get('/driver-violations/{driverViolation}/action-plan/evidence', [DriverViolationController::class, 'downloadActionEvidence'])
        ->name('violations.action-plan.evidence');
    Route::get('/driver-violations/{driverViolation}/report', [DriverViolationController::class, 'downloadReport'])
        ->name('violations.report');

    // Organigram
    Route::get('organigram/download', [OrganigramMemberController::class, 'download'])->name('organigram.download');
    Route::resource('organigram', OrganigramMemberController::class)
        ->except(['show', 'create', 'edit']);

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
    
    // Administration Roles
    Route::get('/administration-roles', [AdministrationRoleController::class, 'index'])->name('administration-roles.index');
    Route::get('/administration-roles/create', [AdministrationRoleController::class, 'create'])->name('administration-roles.create');
    Route::post('/administration-roles', [AdministrationRoleController::class, 'store'])->name('administration-roles.store');
    Route::get('/administration-roles/export', [AdministrationRoleController::class, 'export'])->name('administration-roles.export');
    Route::get('/administration-roles/terminated', [AdministrationRoleController::class, 'terminated'])->name('administration-roles.terminated');
    Route::get('/administration-roles/{user}/edit', [AdministrationRoleController::class, 'edit'])->name('administration-roles.edit');
    Route::put('/administration-roles/{user}', [AdministrationRoleController::class, 'update'])->name('administration-roles.update');
    Route::get('/administration-roles/{user}', [AdministrationRoleController::class, 'show'])->name('administration-roles.show');
    Route::post('/administration-roles/{user}/update-status', [AdministrationRoleController::class, 'updateStatus'])->name('administration-roles.update-status');

    // TBT Formations
    Route::get('/tbt-formations/planning', [TbtFormationController::class, 'planning'])->name('tbt-formations.planning');
    Route::get('/tbt-formations/planning/pdf', [TbtFormationController::class, 'planningPdf'])->name('tbt-formations.planning.pdf');
    Route::resource('tbt-formations', TbtFormationController::class)->except(['show']);
    Route::post('/tbt-formations/{tbtFormation}/mark-realized', [TbtFormationController::class, 'markAsRealized'])->name('tbt-formations.mark-realized');
    
    // Coaching Cabines
    Route::get('/coaching-cabines/planning/{year?}', [CoachingCabineController::class, 'planning'])->name('coaching-cabines.planning');
    Route::get('/coaching-cabines/planning/{year}/pdf', [CoachingCabineController::class, 'planningPdf'])->name('coaching-cabines.planning.pdf');
    Route::get('/coaching-cabines/{coachingCabine}/pdf', [CoachingCabineController::class, 'pdf'])->name('coaching-cabines.pdf');
    Route::put('/coaching-cabines/{coachingCabine}/complete', [CoachingCabineController::class, 'complete'])->name('coaching-cabines.complete');
    Route::resource('coaching-cabines', CoachingCabineController::class);

    // Driver Handovers
    Route::get('/driver-handovers/export', [DriverHandoverController::class, 'export'])->name('driver-handovers.export');
    Route::resource('driver-handovers', DriverHandoverController::class);
    Route::post('/driver-handovers/{driver_handover}/confirm', [DriverHandoverController::class, 'confirm'])->name('driver-handovers.confirm');

    // Export Center
    Route::get('/export-center', [\App\Http\Controllers\ExportCenterController::class, 'index'])->name('export-center.index');
    Route::get('/export-center/violations/export', [\App\Http\Controllers\ExportCenterController::class, 'exportViolations'])->name('export-center.violations.export');
    Route::get('/export-center/violations/export-pdf', [\App\Http\Controllers\ExportCenterController::class, 'exportViolationsPdf'])->name('export-center.violations.export-pdf');
    Route::get('/export-center/driving-times/export', [\App\Http\Controllers\ExportCenterController::class, 'exportDrivingTimes'])->name('export-center.driving-times.export');
    Route::get('/export-center/driving-times/export-pdf', [\App\Http\Controllers\ExportCenterController::class, 'exportDrivingTimesPdf'])->name('export-center.driving-times.export-pdf');
    
});

require __DIR__.'/auth.php';
