<?php
/**
 * Watchtower Observability System Routes
 * Monitors: API, Queue, Database, Performance, Plugins,
 *           Errors, Notifications, Security, Safety Engine, System
 * Auth: auth:sanctum + admin middleware
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Watchtower\ApiMonitorController;
use App\Http\Controllers\Watchtower\QueueMonitorController;
use App\Http\Controllers\Watchtower\DatabaseMonitorController;
use App\Http\Controllers\Watchtower\PluginHealthController;
use App\Http\Controllers\Watchtower\SafetyEngineMonitorController;
use App\Http\Controllers\Watchtower\PerformanceMonitorController;
use App\Http\Controllers\Watchtower\ErrorMonitorController;
use App\Http\Controllers\Watchtower\NotificationMonitorController;
use App\Http\Controllers\Watchtower\SecurityMonitorController;
use App\Http\Controllers\Watchtower\WatchtowerHealthController;

Route::middleware(['auth:sanctum', 'admin'])
    ->prefix('watchtower')
    ->group(function () {

        Route::get('/health', [HealthController::class, 'index']);

        Route::prefix('api')->group(function () {
            Route::get('/metrics', [ApiMonitorController::class, 'metrics']);
            Route::get('/degradation', [ApiMonitorController::class, 'degradation']);
        });

        Route::prefix('queue')->group(function () {
            Route::get('/metrics', [QueueMonitorController::class, 'metrics']);
            Route::get('/stuck', [QueueMonitorController::class, 'stuck']);
        });

        Route::prefix('database')->group(function () {
            Route::get('/metrics', [DatabaseMonitorController::class, 'metrics']);
            Route::get('/locks', [DatabaseMonitorController::class, 'locks']);
        });

        Route::prefix('plugins')->group(function () {
            Route::get('/', [PluginHealthController::class, 'all']);
            Route::get('/{name}', [PluginHealthController::class, 'show']);
        });

        Route::prefix('safety')->group(function () {
            Route::get('/metrics', [SafetyEngineMonitorController::class, 'metrics']);
        });

        Route::prefix('performance')->group(function () {
            Route::get('/metrics', [PerformanceMonitorController::class, 'metrics']);
        });

        Route::prefix('errors')->group(function () {
            Route::get('/metrics', [ErrorMonitorController::class, 'metrics']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/metrics', [NotificationMonitorController::class, 'metrics']);
        });

        Route::prefix('security')->group(function () {
            Route::get('/metrics', [SecurityMonitorController::class, 'metrics']);
        });

        Route::prefix('system')->group(function () {
            Route::get('/health', [WatchtowerHealthController::class, 'index']);
        });
    });
