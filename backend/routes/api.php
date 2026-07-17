<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\CheckInController;
use App\Http\Controllers\Api\V1\AssistanceController;
use App\Http\Controllers\Api\V1\SosController;
use App\Http\Controllers\Api\V1\CheckInSettingsController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\ReminderController;
use App\Http\Controllers\Api\V1\DuressPinController;
use App\Http\Controllers\Api\V1\TrustedContactController;
use App\Http\Controllers\Api\V1\ActivitiesController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\IncidentController;
use App\Http\Controllers\Api\V1\OnboardingDraftController;

Route::prefix('v1')->group(function () {
    Route::get('/location', [LocationController::class, 'show']);
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:otp');
    Route::post('/auth/confirm-phone', [AuthController::class, 'confirmPhone']);
    Route::post('/auth/create-pin', [AuthController::class, 'createPin']);
    Route::post('/auth/login-pin', [AuthController::class, 'loginPin']);
   Route::post('/sos', [SosController::class, 'store'])->middleware('auth:sanctum');


 
Route::get('/trusted-contacts', [TrustedContactController::class, 'index'])->middleware('auth:sanctum');
Route::post('/trusted-contacts', [TrustedContactController::class, 'store'])->middleware('auth:sanctum');
Route::delete('/trusted-contacts/{id}', [TrustedContactController::class, 'destroy'])->middleware('auth:sanctum');

    Route::get('/incidents', [IncidentController::class, 'index'])->middleware('auth:sanctum');
Route::get('/incidents/{id}', [IncidentController::class, 'show'])->middleware('auth:sanctum');
Route::post('/incidents/{id}/resolve', [IncidentController::class, 'markResolved'])->middleware('auth:sanctum');
    Route::get('/trusted-contact/notifications/{phone}', [IncidentController::class, 'notifications']);

    Route::post('/auth/user-details', [AuthController::class, 'userDetails']);
    Route::post('/auth/trusted-contact', [AuthController::class, 'saveTrustedContact']);
    Route::post('/auth/complete-onboarding', [AuthController::class, 'completeOnboarding']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/activities', [DashboardController::class, 'activities']);
        Route::post('/checkin', [CheckInController::class, 'store']);
        Route::post('/assistance', [AssistanceController::class, 'store']);
        Route::get('/checkin-settings', [CheckInSettingsController::class, 'get']);
        Route::post('/checkin-settings', [CheckInSettingsController::class, 'update']);
        Route::get('/duress-pin', [DuressPinController::class, 'status']);
        Route::post('/duress-pin', [DuressPinController::class, 'store']);
        Route::delete('/duress-pin', [DuressPinController::class, 'destroy']);
        Route::get('/activities', [ActivitiesController::class, 'index']);
        
        // Onboarding draft routes
        Route::get('/onboarding/draft', [OnboardingDraftController::class, 'get']);
        Route::post('/onboarding/draft', [OnboardingDraftController::class, 'store']);
    });

    Route::post('/forgot-pin/send-otp', [PasswordResetController::class, 'sendOtp'])->middleware('throttle:otp');
    Route::post('/forgot-pin/verify-otp', [PasswordResetController::class, 'verifyOtp'])->middleware('throttle:otp');
    Route::post('/forgot-pin/reset', [PasswordResetController::class, 'resetPin'])->middleware('throttle:otp');
    Route::get('/health', [HealthController::class, 'index']);
    Route::get('/ping', [HealthController::class, 'ping']);
    Route::get('/check-reminder', [ReminderController::class, 'check']);
});

// ============================================================
// Sync Endpoint (catch-all for queued items)
// ============================================================
Route::post('/sync', function (Request $request) {
    $data = $request->all();
    $type = $data['type'] ?? 'unknown';
    
    // Route to appropriate handler based on type
    if ($type === 'sos') {
        // Forward to SOS controller
        $controller = new App\Http\Controllers\Api\V1\SosController();
        return $controller->store($request);
    } elseif ($type === 'checkin') {
        // Forward to CheckIn controller
        $controller = new App\Http\Controllers\Api\V1\CheckInController();
        return $controller->store($request);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Item queued for processing',
        'data' => $data
    ]);
});

// ============================================================
// Sync Endpoint (catch-all for queued items)
// ============================================================
Route::post('/sync', function (Request $request) {
    $data = $request->all();
    $type = $data['type'] ?? 'unknown';
    
    // Route to appropriate handler based on type
    if ($type === 'sos') {
        $controller = new App\Http\Controllers\Api\V1\SosController();
        return $controller->store($request);
    } elseif ($type === 'checkin') {
        $controller = new App\Http\Controllers\Api\V1\CheckInController();
        return $controller->store($request);
    }
    
    return response()->json([
        'success' => true,
        'message' => 'Item queued for processing',
        'data' => $data
    ]);
})->middleware('auth:sanctum');

// Health endpoint for NetworkDetection
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Watchtower Observability System
require_once __DIR__.'/watchtower.php';

// Safe Zones
Route::prefix('v1/safe-zones')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\V1\SafeZoneController::class, 'index']);
    Route::post('/', [App\Http\Controllers\Api\V1\SafeZoneController::class, 'store']);
    Route::patch('/{id}', [App\Http\Controllers\Api\V1\SafeZoneController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\Api\V1\SafeZoneController::class, 'destroy']);
});

// Trusted Contact Verification
Route::post('/trusted-contacts/verify', [App\Http\Controllers\Api\V1\VerificationController::class, 'verify']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/trusted-contacts/{id}/resend', [App\Http\Controllers\Api\V1\VerificationController::class, 'resend']);
    Route::patch('/trusted-contacts/{id}', [App\Http\Controllers\Api\V1\TrustedContactController::class, 'update']);
});

Route::get('/v1/announcements', [App\Http\Controllers\Api\V1\AnnouncementController::class, 'index']);

Route::get('/v1/version', [App\Http\Controllers\Api\V1\VersionController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/v1/notifications', [App\Http\Controllers\Api\V1\NotificationController::class, 'index']);
    Route::get('/v1/notifications/unread-count', [App\Http\Controllers\Api\V1\NotificationController::class, 'unreadCount']);
});
