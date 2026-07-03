<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\SystemSettingsController;

// Admin Routes
Route::prefix('admin')->group(function () {
    // Public admin routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');

    // Protected admin routes
    Route::middleware('admin.auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::get('/audit', [AuditController::class, 'index'])->name('admin.audit.index');
        Route::get('/settings', [SystemSettingsController::class, 'index'])->name('admin.settings.index');
    });
});
