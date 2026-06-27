<?php

namespace App\Services;

use App\Models\User;
use App\Models\CheckIn;
use App\Models\CheckinSetting;
use App\Services\SafetyScoreService;
use Illuminate\Support\Facades\Log;

class CheckInService
{
    protected $safetyScoreService;

    public function __construct(SafetyScoreService $safetyScoreService)
    {
        $this->safetyScoreService = $safetyScoreService;
    }

    public function createCheckIn(User $user, string $status, ?array $location = null): array
    {
        try {
            // Validate status
            if (!in_array($status, ['safe', 'unsafe'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid status. Must be "safe" or "unsafe".',
                    'code' => 'INVALID_STATUS',
                ];
            }

            // Check if user has trusted contacts (already done in controller)

            // Get settings
            $settings = CheckinSetting::where('user_id', $user->id)->first();

            // Create check-in
            $checkIn = CheckIn::create([
                'user_id' => $user->id,
                'status' => $status,
                'checked_in_at' => now(),
                'latitude' => $location['lat'] ?? null,
                'longitude' => $location['lng'] ?? null,
                'accuracy' => $location['accuracy'] ?? null,
            ]);

            // Update safety score
            $score = $this->safetyScoreService->updateForUser($user);

            return [
                'success' => true,
                'check_in' => $checkIn,
                'confidence' => $score,
                'message' => 'Check-in recorded successfully.',
            ];

        } catch (\Exception $e) {
            Log::error('CheckInService error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'An error occurred while creating check-in: ' . $e->getMessage(),
                'code' => 'SERVICE_ERROR',
            ];
        }
    }
}
