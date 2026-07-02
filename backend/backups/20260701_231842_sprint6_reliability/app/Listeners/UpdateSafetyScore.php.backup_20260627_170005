<?php

namespace App\Listeners;

use App\Events\CheckInCompleted;
use App\Events\SOSTriggered;
use App\Services\SafetyScoreService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateSafetyScore implements ShouldQueue
{
    private SafetyScoreService $safetyScoreService;

    public function __construct(SafetyScoreService $safetyScoreService)
    {
        $this->safetyScoreService = $safetyScoreService;
    }

    public function handle($event)
    {
        $userId = $event->checkIn->user_id ?? $event->sos->user_id ?? null;
        
        if ($userId) {
            $this->safetyScoreService->calculateScore($userId);
        }
    }
}
