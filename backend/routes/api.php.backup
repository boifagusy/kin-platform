<?php


use Illuminate\Support\Facades\Route;

// Test route
Route::get('/test-watchtower', function () {
    return ['status' => 'ok'];
});

// ============================================================
// Watchtower Observability System Routes
// ============================================================

// Public health endpoint (no auth) - outside watchtower prefix
Route::get('/health', [App\Http\Controllers\HealthController::class, 'index']);

// Watchtower routes (require admin auth)
Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower')->group(function () {
    Route::get('/health', [App\Http\Controllers\HealthController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/api')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\ApiMonitorController::class, 'metrics']);
    Route::get('/degradation', [App\Http\Controllers\Watchtower\ApiMonitorController::class, 'degradation']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/queue')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\QueueMonitorController::class, 'metrics']);
    Route::get('/stuck', [App\Http\Controllers\Watchtower\QueueMonitorController::class, 'stuck']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/database')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\DatabaseMonitorController::class, 'metrics']);
    Route::get('/locks', [App\Http\Controllers\Watchtower\DatabaseMonitorController::class, 'locks']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/plugins')->group(function () {
    Route::get('/', [App\Http\Controllers\Watchtower\PluginHealthController::class, 'all']);
    Route::get('/{name}', [App\Http\Controllers\Watchtower\PluginHealthController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/safety')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\SafetyEngineMonitorController::class, 'metrics']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/performance')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\PerformanceMonitorController::class, 'metrics']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/errors')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\ErrorMonitorController::class, 'metrics']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/notifications')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\NotificationMonitorController::class, 'metrics']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/security')->group(function () {
    Route::get('/metrics', [App\Http\Controllers\Watchtower\SecurityMonitorController::class, 'metrics']);
});

// Watchtower Self-Monitoring
Route::middleware(['auth:sanctum', 'admin'])->prefix('watchtower/system')->group(function () {
    Route::get('/health', [App\Http\Controllers\Watchtower\WatchtowerHealthController::class, 'index']);
});
