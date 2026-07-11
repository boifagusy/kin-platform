#!/data/data/com.termux/files/usr/bin/bash
# Watchtower Restoration Recipe v2.0
# SDK Recipe - Reusable across KIN, VinePay, FlashFlow, HyperMind

# Recipe metadata
RECIPE_NAME="Watchtower Observability Platform"
RECIPE_VERSION="2.0.0"
RECIPE_DESCRIPTION="API, Queue, Database, Performance, Plugin, Error, Notification, Security, and System monitoring"
RECIPE_DEPENDENCIES=("authentication" "admin")

# Project paths (relative to project root)
PROJECT_ROOT="${KIN_PROJECT_ROOT:-$HOME/kin_project/backend}"
ROUTE_FILE="$PROJECT_ROOT/routes/watchtower.php"
API_FILE="$PROJECT_ROOT/routes/api.php"

CREATE_FILES=("$ROUTE_FILE")
MODIFY_FILES=("$API_FILE")

EXPECTED_ROUTES=(
    "watchtower/health"
    "watchtower/api/metrics"
    "watchtower/api/degradation"
    "watchtower/queue/metrics"
    "watchtower/queue/stuck"
    "watchtower/database/metrics"
    "watchtower/database/locks"
    "watchtower/plugins"
    "watchtower/safety/metrics"
    "watchtower/performance/metrics"
    "watchtower/errors/metrics"
    "watchtower/notifications/metrics"
    "watchtower/security/metrics"
    "watchtower/system/health"
)

SAFETY_ROUTES=(
    "dashboard" "activities" "incidents" "checkin"
    "trusted-contacts" "sos" "health" "auth/login"
)

# ============================================
# PRE-RESTORE
# ============================================
pre_restore() {
    # Idempotency check
    if [ -f "$ROUTE_FILE" ] && grep -q "require_once.*watchtower.php" "$API_FILE" 2>/dev/null; then
        echo "⚠ Watchtower already installed"
        return 1
    fi
    echo "✓ Clean state confirmed"
    return 0
}

# ============================================
# RESTORE
# ============================================
restore() {
    local manifest="$1"
    
    # Create route file (single middleware group)
    cat > "$ROUTE_FILE" << 'ROUTES'
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
ROUTES

    # Validate syntax
    if ! php -l "$ROUTE_FILE" 2>&1 | grep -q "No syntax errors"; then
        echo "✗ Syntax error"
        rm "$ROUTE_FILE"
        return 1
    fi
    echo "✓ Created: $ROUTE_FILE"

    # Backup and register
    local backup="${API_FILE}.backup_$(date +%Y%m%d_%H%M%S)"
    cp "$API_FILE" "$backup"
    echo "✓ Backup: $backup"

    if ! grep -q "require_once.*watchtower.php" "$API_FILE"; then
        echo "" >> "$API_FILE"
        echo "// Watchtower Observability System" >> "$API_FILE"
        echo "require_once __DIR__.'/watchtower.php';" >> "$API_FILE"
        echo "✓ Registered in api.php"
    fi

    cd "$PROJECT_ROOT" && php artisan optimize:clear 2>&1 > /dev/null
    echo "✓ Cache cleared"
    return 0
}

# ============================================
# VERIFY
# ============================================
verify() {
    cd "$PROJECT_ROOT"
    local errors=0

    for endpoint in "${EXPECTED_ROUTES[@]}"; do
        if php artisan route:list 2>&1 | grep -q "$endpoint"; then
            echo "  ✓ $endpoint"
        else
            echo "  ✗ $endpoint MISSING"
            errors=$((errors + 1))
        fi
    done

    for route in "${SAFETY_ROUTES[@]}"; do
        if php artisan route:list 2>&1 | grep -q "$route"; then
            echo "  ✓ $route (safety)"
        else
            echo "  ✗ REGRESSION: $route"
            errors=$((errors + 1))
        fi
    done

    if php artisan route:cache 2>&1; then
        echo "  ✓ Route cache OK"
        php artisan route:clear 2>&1 > /dev/null
    else
        echo "  ✗ Route cache failed"
        php artisan route:clear 2>&1 > /dev/null
        errors=$((errors + 1))
    fi

    return $errors
}

# ============================================
# ROLLBACK
# ============================================
rollback() {
    cd "$PROJECT_ROOT"
    sed -i '/require_once.*watchtower.php/d' "$API_FILE" 2>/dev/null
    sed -i '/Watchtower Observability System/d' "$API_FILE" 2>/dev/null
    rm -f "$ROUTE_FILE"
    php artisan optimize:clear 2>&1 > /dev/null
    echo "✓ Watchtower routes removed"
}
