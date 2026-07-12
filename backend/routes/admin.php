<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\WatchtowerDashboardController;
use App\Http\Controllers\Admin\SafetyMonitorController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\SystemSettingsController;

Route::prefix('admin')->group(function () {
    // Public admin routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/watchtower', [WatchtowerDashboardController::class, 'index'])->name('admin.watchtower');
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
    });
});

// Email Settings (outside admin prefix)
Route::prefix('admin')->group(function () {
    Route::get('/settings/email', [SystemSettingsController::class, 'emailSettings'])->name('admin.settings.email');
    Route::put('/settings/email', [SystemSettingsController::class, 'updateEmailSettings'])->name('admin.settings.email.update');
    Route::post('/settings/email/test', [SystemSettingsController::class, 'testEmail'])->name('admin.settings.email.test');
});
Route::get('/debug-sidebar', function() {
    return view('admin.debug-sidebar');
})->middleware('admin.auth');

// Admin Password Reset - Must be inside admin prefix
Route::prefix('admin')->group(function () {
});

// Admin Password Reset (OTP based)

// OTP verification endpoint

// Ensure JSON responses for API calls

// OTP verification page

// =============================================
// ADMIN PASSWORD RESET (Canonical Routes)
// =============================================
// Step 1: Request OTP (email form)
Route::get('/forgot-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'showForgotForm'])->name('admin.password.request');
Route::post('/forgot-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'sendOtp'])->name('admin.password.email');

// Step 2: Verify OTP
Route::get('/verify-otp', [App\Http\Controllers\Admin\PasswordResetController::class, 'showOtpForm'])->name('admin.password.otp.form');
Route::post('/verify-otp', [App\Http\Controllers\Admin\PasswordResetController::class, 'verifyOtp'])->name('admin.password.verify');

// Step 3: Reset password
Route::get('/reset-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'showResetForm'])->name('admin.password.reset.form');
Route::post('/reset-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'reset'])->name('admin.password.update');

// Admin Management

// Admin Management (with admin prefix)
Route::prefix('admin')->group(function () {
    Route::prefix('admins')->middleware(['admin.auth'])->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AdminManagementController::class, 'index'])->name('admin.admins.index');
        Route::get('/create', [App\Http\Controllers\Admin\AdminManagementController::class, 'create'])->name('admin.admins.create');
        Route::post('/', [App\Http\Controllers\Admin\AdminManagementController::class, 'store'])->name('admin.admins.store');
        Route::get('/{id}', [App\Http\Controllers\Admin\AdminManagementController::class, 'show'])->name('admin.admins.show');
        Route::get('/{id}/edit', [App\Http\Controllers\Admin\AdminManagementController::class, 'edit'])->name('admin.admins.edit');
        Route::put('/{id}', [App\Http\Controllers\Admin\AdminManagementController::class, 'update'])->name('admin.admins.update');
        Route::post('/{id}/activate', [App\Http\Controllers\Admin\AdminManagementController::class, 'activate'])->name('admin.admins.activate');
        Route::post('/{id}/deactivate', [App\Http\Controllers\Admin\AdminManagementController::class, 'deactivate'])->name('admin.admins.deactivate');
    });
});

// Admin Password Reset Routes
Route::get('/forgot-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'showForgotForm'])->name('admin.password.request');
Route::post('/forgot-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'sendOtp'])->name('admin.password.email');
Route::get('/verify-otp', [App\Http\Controllers\Admin\PasswordResetController::class, 'showOtpForm'])->name('admin.password.otp.form');
Route::post('/verify-otp', [App\Http\Controllers\Admin\PasswordResetController::class, 'verifyOtp'])->name('admin.password.verify');
Route::get('/reset-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'showResetForm'])->name('admin.password.reset.form');
Route::post('/reset-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'reset'])->name('admin.password.update');

// Admin Password Reset (with admin prefix)
Route::get('/forgot-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'showForgotForm'])->name('admin.password.request');
Route::post('/forgot-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'sendOtp'])->name('admin.password.email');
Route::get('/verify-otp', [App\Http\Controllers\Admin\PasswordResetController::class, 'showOtpForm'])->name('admin.password.otp.form');
Route::post('/verify-otp', [App\Http\Controllers\Admin\PasswordResetController::class, 'verifyOtp'])->name('admin.password.verify');
Route::get('/reset-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'showResetForm'])->name('admin.password.reset.form');
Route::post('/reset-password', [App\Http\Controllers\Admin\PasswordResetController::class, 'reset'])->name('admin.password.update');
