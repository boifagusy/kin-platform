<?php

namespace App\Listeners;

use App\Events\CheckInCompleted;
use App\Events\SOSTriggered;
use App\Services\SafetyScoreService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateSafetyScore implements ShouldQueue
{
    private SafetyScoreService $safetyScoreService;

    public function __construct(SafetyScoreService $safetyScoreService)
    {
        $this->safetyScoreService = $safetyScoreService;
    }

    public function handle($event)
    {
        try {
            $userId = null;

            // Get user from CheckInCompleted event
            if ($event instanceof CheckInCompleted && isset($event->checkIn)) {
                $userId = $event->checkIn->user_id;
            }

            // Get user from SOSTriggered event
            if ($event instanceof SOSTriggered) {
                if (isset($event->sos) && isset($event->sos->user_id)) {
                    $userId = $event->sos->user_id;
                } elseif (isset($event->user_id)) {
                    $userId = $event->user_id;
                }
            }

            if (!$userId) {
                Log::warning('UpdateSafetyScore: No user ID found', [
                    'event_type' => get_class($event)
                ]);
                return;
            }

            // ✅ FIXED: Use updateForUser() not calculateScore()
            $user = \App\Models\User::find($userId);
            if ($user) {
                $score = $this->safetyScoreService->updateForUser($user);
                
                Log::info('Safety score updated', [
                    'user_id' => $user->id,
                    'score' => $score,
                    'event_type' => get_class($event)
                ]);
            }

        } catch (\Exception $e) {
            Log::error('UpdateSafetyScore failed: ' . $e->getMessage(), [
                'event_type' => get_class($event),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
