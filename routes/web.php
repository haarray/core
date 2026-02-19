<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UiOptionsController;

// ── Public routes ─────────────────────────────────────────
Route::get('/',       fn() => redirect('/login'));
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/auth/facebook/redirect', [AuthController::class, 'facebookRedirect'])->name('facebook.redirect');
Route::get('/auth/facebook/callback', [AuthController::class, 'facebookCallback'])->name('facebook.callback');
Route::get('/2fa', [AuthController::class, 'showTwoFactor'])->name('2fa.form');
Route::post('/2fa/verify', [AuthController::class, 'verifyTwoFactor'])->name('2fa.verify');
Route::post('/2fa/resend', [AuthController::class, 'resendTwoFactor'])->name('2fa.resend');

// ── Protected routes ──────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view dashboard')
        ->name('dashboard');

    Route::get('/docs', fn() => view('docs.starter-kit'))
        ->middleware('permission:view docs')
        ->name('docs.index');

    Route::get('/docs/starter-kit', fn() => redirect()->route('docs.index'))
        ->middleware('permission:view docs')
        ->name('docs.starter');

    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('permission:view settings')
        ->name('settings.index');

    Route::post('/settings', [SettingsController::class, 'update'])
        ->middleware('permission:manage settings')
        ->name('settings.update');

    Route::post('/settings/security', [SettingsController::class, 'updateMySecurity'])
        ->middleware('permission:view settings')
        ->name('settings.security');

    Route::post('/settings/users/{user}/access', [SettingsController::class, 'updateUserAccess'])
        ->middleware('permission:manage users')
        ->name('settings.users.access');

    Route::get('/settings/users/{user}/access', fn () => redirect()->route('settings.index'))
        ->middleware('permission:view settings');

    Route::post('/settings/roles/matrix', [SettingsController::class, 'updateRoleMatrix'])
        ->middleware('permission:manage settings')
        ->name('settings.roles.matrix');

    Route::post('/settings/ops/action', [SettingsController::class, 'runOpsAction'])
        ->middleware('permission:manage settings')
        ->name('settings.ops.action');

    Route::post('/settings/ml/probe', [SettingsController::class, 'runMlProbe'])
        ->middleware('permission:manage settings')
        ->name('settings.ml.probe');

    Route::get('/ui/options/leads', [UiOptionsController::class, 'leads'])
        ->middleware('permission:view users')
        ->name('ui.options.leads');

    Route::get('/ui/datatables/users', [UiOptionsController::class, 'usersTable'])
        ->middleware('permission:manage users')
        ->name('ui.datatables.users');

    Route::get('/notifications/feed', [NotificationController::class, 'feed'])
        ->middleware('permission:view notifications')
        ->name('notifications.feed');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->middleware('permission:view notifications')
        ->name('notifications.read');

    Route::post('/notifications/broadcast', [NotificationController::class, 'broadcast'])
        ->middleware('permission:manage notifications')
        ->name('notifications.broadcast');

    Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
});
