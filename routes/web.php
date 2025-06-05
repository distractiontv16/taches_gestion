<?php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\ChecklistItemController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\NoteController;

use App\Http\Controllers\ReminderController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Routes de vérification d'email
Route::get('/email/verify', [VerificationController::class, 'show'])->middleware(['auth'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', [VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Routes de récupération de mot de passe
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::controller(MailController::class)->prefix('mail')->name('mail.')->group(function () {
        Route::get('/', 'index')->name('inbox');
    });


    Route::resource('tasks', TaskController::class);
    Route::post('tasks/{task}/update-status', [TaskController::class, 'updateStatus']);
    Route::post('tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])->name('tasks.toggle-complete');
    
    Route::resource('routines', RoutineController::class)->except(['show']);
    Route::get('routines/showAll', [RoutineController::class, 'showAll'])->name('routines.showAll');
    Route::get('routines/daily', [RoutineController::class, 'showDaily'])->name('routines.showDaily');
    Route::get('routines/weekly', [RoutineController::class, 'showWeekly'])->name('routines.showWeekly');
    Route::get('routines/monthly', [RoutineController::class, 'showMonthly'])->name('routines.showMonthly');
    Route::get('routines/{routine}/preview', [RoutineController::class, 'preview'])->name('routines.preview');
    Route::post('routines/{routine}/toggle-active', [RoutineController::class, 'toggleActive'])->name('routines.toggleActive');
    Route::resource('files', FileController::class);
    Route::get('files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::resource('notes', NoteController::class);
    Route::resource('reminders', ReminderController::class);
    Route::resource('checklist-items', ChecklistItemController::class);
    Route::get('checklist-items/{checklistItem}/update-status', [ChecklistItemController::class, 'updateStatus'])->name('checklist-items.update-status');
    
    // Routes pour la page de paramètres
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::post('/settings/appearance', [SettingsController::class, 'updateAppearance'])->name('settings.appearance');
    Route::post('/settings/security', [SettingsController::class, 'updateSecurity'])->name('settings.security');
    
    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});
