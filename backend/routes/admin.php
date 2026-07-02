<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SafetyMonitorController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Sentinel\SecurityDashboardController;
use App\Http\Controllers\Sentinel\ComplianceController;
use App\Http\Controllers\Sentinel\SentinelDashboardController;

Route::prefix('admin')->group(function () {
    // Public admin routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

        // Safety Monitor API
        Route::get('/safety/metrics', [SafetyMonitorController::class, 'getMetrics'])->name('admin.safety.metrics');
        Route::get('/safety/trend', [SafetyMonitorController::class, 'getTrendData'])->name('admin.safety.trend');

        // User Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{id}', [UserManagementController::class, 'show'])->name('admin.users.show');
        Route::post('/users/{id}/activate', [UserManagementController::class, 'activate'])->name('admin.users.activate');
        Route::post('/users/{id}/suspend', [UserManagementController::class, 'suspend'])->name('admin.users.suspend');
        Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/{id}/restore', [UserManagementController::class, 'restore'])->name('admin.users.restore');
        Route::post('/users/bulk', [UserManagementController::class, 'bulkAction'])->name('admin.users.bulk');

        // Alert Management
        Route::get('/alerts', [SafetyMonitorController::class, 'alertsIndex'])->name('admin.alerts.index');
        Route::get('/alerts/{id}', [SafetyMonitorController::class, 'alertsShow'])->name('admin.alerts.show');
        Route::post('/alerts/{id}/assign', [SafetyMonitorController::class, 'assignAlert'])->name('admin.alerts.assign');
        Route::post('/alerts/{id}/resolve', [SafetyMonitorController::class, 'resolveAlert'])->name('admin.alerts.resolve');
        Route::post('/alerts/{id}/escalate', [SafetyMonitorController::class, 'escalateAlert'])->name('admin.alerts.escalate');
        Route::post('/alerts/{id}/note', [SafetyMonitorController::class, 'addAlertNote'])->name('admin.alerts.add-note');

        // Admin Management
        Route::prefix('admins')->group(function () {
            Route::get('/', [AdminManagementController::class, 'index'])->name('admin.admins.index');
            Route::get('/create', [AdminManagementController::class, 'create'])->name('admin.admins.create');
            Route::post('/', [AdminManagementController::class, 'store'])->name('admin.admins.store');
            Route::get('/{id}', [AdminManagementController::class, 'show'])->name('admin.admins.show');
            Route::get('/{id}/edit', [AdminManagementController::class, 'edit'])->name('admin.admins.edit');
            Route::put('/{id}', [AdminManagementController::class, 'update'])->name('admin.admins.update');
            Route::post('/{id}/activate', [AdminManagementController::class, 'activate'])->name('admin.admins.activate');
            Route::post('/{id}/deactivate', [AdminManagementController::class, 'deactivate'])->name('admin.admins.deactivate');
        });

        // Audit
        Route::get('/audit', [AuditController::class, 'index'])->name('admin.audit.index');
        Route::get('/audit/export', [AuditController::class, 'export'])->name('admin.audit.export');

        // ============================================================
        // SENTINEL ROUTES (Security Operations Center)
        // ============================================================
        Route::prefix('sentinel')->group(function () {
            // SOC Dashboard (new)
            Route::get('/dashboard', [SentinelDashboardController::class, 'index'])->name('sentinel.dashboard');
            Route::get('/metrics', [SentinelDashboardController::class, 'metrics'])->name('sentinel.metrics');
            Route::get('/threats', [SentinelDashboardController::class, 'threats'])->name('sentinel.threats');
            Route::get('/high-risk-users', [SentinelDashboardController::class, 'highRiskUsers'])->name('sentinel.high-risk-users');
            Route::get('/timeline', [SentinelDashboardController::class, 'timeline'])->name('sentinel.timeline');
            Route::get('/charts', [SentinelDashboardController::class, 'charts'])->name('sentinel.charts');

            // Security Dashboard (original)
            Route::get('/security-dashboard', [SecurityDashboardController::class, 'index'])->name('sentinel.security-dashboard');
            Route::get('/events', [SecurityDashboardController::class, 'events'])->name('sentinel.events');
            Route::get('/settings', [SecurityDashboardController::class, 'settings'])->name('sentinel.settings');

            // Compliance
            Route::get('/compliance', [ComplianceController::class, 'index'])->name('sentinel.compliance');
            Route::get('/compliance/{type}', [ComplianceController::class, 'show'])->name('sentinel.compliance.show');
            Route::get('/security', [ComplianceController::class, 'security'])->name('sentinel.security');
        });

        // ============================================================
        // WATCHTOWER ROUTES
        // ============================================================
        Route::prefix('watchtower')->group(function () {
            Route::get('/overview', function () {
                return view('admin.watchtower.overview');
            })->name('admin.watchtower.overview');

            Route::get('/incidents', function () {
                return view('admin.watchtower.incidents');
            })->name('admin.watchtower.incidents');

            Route::get('/alert-rules', function () {
                return view('admin.watchtower.alert-rules');
            })->name('admin.watchtower.alert-rules');
        });

        // Settings
        Route::get('/settings', [SystemSettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/settings', [SystemSettingsController::class, 'update'])->name('admin.settings.update');
    });
});

// Admin Password Reset Routes (outside admin.auth)
Route::prefix('admin')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('admin.password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->name('admin.password.email');
    Route::get('/verify-otp', [PasswordResetController::class, 'showOtpForm'])->name('admin.password.otp.form');
    Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('admin.password.verify');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('admin.password.reset.form');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('admin.password.update');
});

// ============================================================
// PULSE ROUTES (Safety Intelligence)
// ============================================================
Route::middleware('admin.auth')->prefix('pulse')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Pulse\PulseDashboardController::class, 'index'])->name('pulse.dashboard');
    Route::get('/metrics', [App\Http\Controllers\Pulse\PulseDashboardController::class, 'metrics'])->name('pulse.metrics');
    Route::get('/events', [App\Http\Controllers\Pulse\PulseDashboardController::class, 'events'])->name('pulse.events');
    Route::get('/high-risk-users', [App\Http\Controllers\Pulse\PulseDashboardController::class, 'highRiskUsers'])->name('pulse.high-risk-users');
});
