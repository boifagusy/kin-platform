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
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('admin.users.show');
});

// Admin User Action Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::post('/users/{id}/suspend', [UserManagementController::class, 'suspend'])->name('admin.users.suspend');
    Route::post('/users/{id}/activate', [UserManagementController::class, 'activate'])->name('admin.users.activate');
});

// Admin Audit Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/audit', [AuditController::class, 'index'])->name('admin.audit.index');
    Route::get('/audit/export', [AuditController::class, 'export'])->name('admin.audit.export');
});

// Admin Settings Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/settings', [SystemSettingsController::class, 'index'])->name('admin.settings.index');
    Route::post('/settings', [SystemSettingsController::class, 'update'])->name('admin.settings.update');
});

// Admin Safety Routes
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    Route::get('/safety/metrics', [SafetyMonitorController::class, 'getMetrics'])->name('admin.safety.metrics');
    Route::get('/safety/trend', [SafetyMonitorController::class, 'getTrendData'])->name('admin.safety.trend');
});

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
