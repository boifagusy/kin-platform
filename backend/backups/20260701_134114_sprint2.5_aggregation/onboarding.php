<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\OnboardingDraftController;

Route::prefix('v1')->group(function () {
    Route::get('/onboarding/draft', [OnboardingDraftController::class, 'get'])
        ->middleware('auth:sanctum');
    Route::post('/onboarding/draft', [OnboardingDraftController::class, 'store'])
        ->middleware('auth:sanctum');
});
