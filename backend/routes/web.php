<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\SafetyMonitorController;

// Welcome page
Route::get('/', function () {
    return view('welcome');
});

// Auth fallback
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Load admin routes from separate file
require __DIR__.'/admin.php';

// Admin User Management Routes

// Admin User Action Routes

// Admin Audit Routes

// Admin Settings Routes

// Admin Safety Routes

// API endpoint to get current settings (for the settings page)
Route::middleware(['web', 'admin.auth'])->get('/admin/api/settings', function() {
    $service = app(App\Services\Admin\SystemSettingsService::class);
    return response()->json(['success' => true, 'settings' => $service->getAllSettings()]);
});

// Settings Hub Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\SystemSettingsController::class, 'index'])->name('index');
    Route::get('/otp', [App\Http\Controllers\Admin\SystemSettingsController::class, 'otp'])->name('otp');
    Route::get('/notifications', [App\Http\Controllers\Admin\SystemSettingsController::class, 'notifications'])->name('notifications');
    Route::get('/security', [App\Http\Controllers\Admin\SystemSettingsController::class, 'security'])->name('security');
    Route::get('/retention', [App\Http\Controllers\Admin\SystemSettingsController::class, 'retention'])->name('retention');
    Route::get('/integrations', [App\Http\Controllers\Admin\SystemSettingsController::class, 'integrations'])->name('integrations');
    Route::get('/features', [App\Http\Controllers\Admin\SystemSettingsController::class, 'features'])->name('features');
});

// Settings Update Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin/settings')->name('admin.settings.')->group(function () {
    Route::post('/otp', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateOtp'])->name('update.otp');
    Route::post('/notifications', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateNotifications'])->name('update.notifications');
    Route::post('/security', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateSecurity'])->name('update.security');
    Route::post('/retention', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateRetention'])->name('update.retention');
});

// Security Settings Save Route
Route::middleware(['web', 'admin.auth'])->post('/admin/settings/security', [App\Http\Controllers\Admin\SystemSettingsController::class, 'updateSecurity'])->name('admin.settings.update.security');

// ============================================================
// SUBSYSTEM ROUTES
// ============================================================

// ============================================================
// SUBSYSTEM ROUTES (Guardian, Pulse, Recovery, Sentinel, Watchtower)
// ============================================================

// Subsystem Routes

// Subsystem Routes (Single source)

// Subsystem Routes (Single source)

// ============================================================
// SUBSYSTEM DASHBOARD ROUTES (Definitive)
// ============================================================

// ============================================================
// SUBSYSTEM DASHBOARD ROUTES (Definitive)
// ============================================================
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/guardian/dashboard', [App\Http\Controllers\Guardian\GuardianDashboardController::class, 'dashboard'])->name('guardian.dashboard');
    Route::get('/pulse/dashboard', [App\Http\Controllers\Pulse\PulseDashboardController::class, 'dashboard'])->name('pulse.dashboard');
    Route::get('/recovery/dashboard', [App\Http\Controllers\Recovery\RecoveryController::class, 'dashboard'])->name('recovery.dashboard');
    Route::get('/sentinel/dashboard', [App\Http\Controllers\Sentinel\SentinelDashboardController::class, 'dashboard'])->name('sentinel.dashboard');
    Route::get('/watchtower/overview', [App\Http\Controllers\Watchtower\DashboardController::class, 'overview'])->name('watchtower.overview');
});

// TEST ROUTE - Confirm routing works
Route::get('/test-subsystem', function() {
    return 'Subsystem routes are working!';
});

// Subsystem Routes (Verified)
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/guardian/dashboard', [App\Http\Controllers\Guardian\GuardianDashboardController::class, 'dashboard'])->name('guardian.dashboard');
    Route::get('/pulse/dashboard', [App\Http\Controllers\Pulse\PulseDashboardController::class, 'dashboard'])->name('pulse.dashboard');
    Route::get('/recovery/dashboard', [App\Http\Controllers\Recovery\RecoveryController::class, 'dashboard'])->name('recovery.dashboard');
    Route::get('/sentinel/dashboard', [App\Http\Controllers\Sentinel\SentinelDashboardController::class, 'dashboard'])->name('sentinel.dashboard');
    Route::get('/watchtower/overview', [App\Http\Controllers\Watchtower\DashboardController::class, 'overview'])->name('watchtower.overview');
});

// Admin Audit Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/audit', [App\Http\Controllers\Admin\AuditController::class, 'index'])->name('admin.audit.index');
    Route::get('/audit/export', [App\Http\Controllers\Admin\AuditController::class, 'export'])->name('admin.audit.export');
});
