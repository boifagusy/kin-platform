<?php

namespace App\Services\Pulse\Rules;

use App\Models\SafetyEvent;
use App\Models\User;
use App\Services\Pulse\SafetyScoreService;

class MissedCheckinRule
{
    protected SafetyScoreService $scoreService;

    public function __construct()
    {
        $this->scoreService = new SafetyScoreService();
    }

    public function detect(User $user): array
    {
        $detections = [];

        // Check for missed check-ins in the last 24 hours
        $missedCheckins = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', 'checkin_missed')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        if ($missedCheckins >= 2) {
            $detections[] = [
                'type' => 'repeated_missed_checkins',
                'severity' => 'high',
                'message' => "User has {$missedCheckins} missed check-ins in 24 hours",
            ];

            // Update safety score
            $this->scoreService->updateScore($user->id, 'checkin_missed', ['count' => $missedCheckins]);
        }

        return $detections;
    }
}
