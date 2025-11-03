<?php

use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\UserController;
// use App\Http\Controllers\TeacherController;
// use App\Http\Controllers\StudentController;
// use App\Http\Controllers\GroupController;
// use App\Http\Controllers\SpicialistController;
// use App\Http\Controllers\CourseController;
// use App\Http\Controllers\ModuleController;
// use App\Http\Controllers\CertificateController;
// use App\Http\Controllers\SchoolSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // responsible links
    // Route::prefix('responsible')->group(function(){
    //     Route::get('/', [UserController::class, 'index'])->name('responsible');
    //     Route::get('/create', [UserController::class, 'create'])->name('responsible.create');
    //     Route::post('/store', [UserController::class, 'store'])->name('responsible.store');
    //     Route::get('/update/{id}', [UserController::class, 'update'])->name('responsible.update');
    //     Route::post('/delete/{id}', [UserController::class, 'destroy'])->name('responsible.destroy');
    // });
    // // teacher links
    // Route::prefix('teacher')->group(function(){
    //     Route::get('/', [TeacherController::class, 'index'])->name('teacher');
    //     Route::get('/create', [TeacherController::class, 'create'])->name('teacher.create');
    //     Route::post('/store', [TeacherController::class, 'store'])->name('teacher.store');
    //     Route::get('/{teacher}', [TeacherController::class, 'show'])->name('teacher.show');
    //     Route::post('/delete/{teacher}', [TeacherController::class, 'destroy'])->name('teacher.destroy');
    // });
    // // module links
    // Route::prefix('modules')->group(function(){
    //     Route::get('/', [ModuleController::class, 'index'])->name('modules');
    //     Route::post('/module', [ModuleController::class, 'store'])->name('modules.store');
    //     Route::patch('/module/{module}', [ModuleController::class, 'update'])->name('module.update');
    //     Route::delete('/module/{module}', [ModuleController::class, 'destroy'])->name('module.destroy');
    // });
    // // course links
    // Route::prefix('courses')->group(function(){
    //     Route::get('/', [CourseController::class, 'index'])->name('courses');
    //     Route::post('/cours', [CourseController::class, 'store'])->name('courses.store');
    //     Route::get('/cours/{teacher}/{group}', [CourseController::class, 'show'])->name('courses.show');
    //     Route::delete('/{teacher}/{group}', [CourseController::class, 'destroy'])->name('courses.destroy');
    //     Route::get('/download/{teacher}/{group}', [CourseController::class, 'download'])->name('courses.download');
    // });
    // // student links
    // Route::prefix('student')->group(function(){
    //     Route::get('/', [StudentController::class, 'index'])->name('student');
    //     Route::get('/create', [StudentController::class, 'create'])->name('student.create');
    //     Route::post('/store', [StudentController::class, 'store'])->name('student.store');
    //     Route::get('/{student}', [StudentController::class, 'show'])->name('student.show');
    //     Route::patch('/{student}/edit', [StudentController::class, 'edit'])->name('student.edit');
    //     Route::patch('/{student}', [StudentController::class, 'update'])->name('student.update');
    //     Route::delete('/{student}', [StudentController::class, 'destroy'])->name('student.destroy');
    //     Route::post('/import', [StudentController::class, 'importExcel'])->name('student.import');
    //     Route::get('/download', [StudentController::class, 'downloadCertificate'])->name('certificate.download');
    //     Route::get('/preview', [StudentController::class, 'previewCertificate'])->name('certificate.preview');
    // });
    // // group links
    // Route::prefix('group')->group(function(){
    //     Route::get('/', [GroupController::class, 'index'])->name('group');
    //     Route::get('/create', [GroupController::class, 'create'])->name('group.create');
    //     Route::post('/store', [GroupController::class, 'store'])->name('group.store');
    //     Route::get('/{group}', [GroupController::class, 'show'])->name('group.show');
    //     Route::put('/{group}', [GroupController::class, 'update'])->name('group.update');
    //     Route::post('/{group}', [GroupController::class, 'destroy'])->name('group.destroy');
    // });
    // // specialist links
    // Route::prefix('specialist')->group(function(){
    //     Route::post('/{group}/teacher', [SpicialistController::class, 'store'])->name('specialist.store');
    //     Route::delete('/{group}/teacher/{teacher}', [SpicialistController::class, 'destroy'])->name('specialist.destroy');
    // });
    // // School settings routes
    // Route::prefix('settings')->group(function(){
    //     Route::get('/', [SchoolSettingsController::class, 'index'])->name('settings');
    //     Route::post('/update', [SchoolSettingsController::class, 'update'])->name('settings.update');
    // })->middleware('can:admin');
});

require __DIR__.'/auth.php';
