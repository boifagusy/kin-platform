<?php

namespace App\Jobs;

use App\Models\EmergencyEscalation;
use App\Models\User;
use App\Services\SafetyScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckEscalationTimeoutsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Find active escalations that have timed out
        $timeouts = EmergencyEscalation::where('status', EmergencyEscalation::STATUS_ACTIVE)
            ->where('timeout_at', '<', now())
            ->get();

        foreach ($timeouts as $escalation) {
            Log::warning('Escalation timed out', [
                'escalation_id' => $escalation->id,
                'user_id' => $escalation->user_id,
                'level' => $escalation->level,
            ]);

            // Auto-escalate to next level
            $this->autoEscalate($escalation);
        }
    }

    private function autoEscalate(EmergencyEscalation $escalation)
    {
        $currentLevel = $escalation->level;

        // Determine next level
        $nextLevel = match($currentLevel) {
            EmergencyEscalation::LEVEL_ORANGE => EmergencyEscalation::LEVEL_RED,
            EmergencyEscalation::LEVEL_RED => EmergencyEscalation::LEVEL_BLACK,
            EmergencyEscalation::LEVEL_BLACK => EmergencyEscalation::LEVEL_BLACK, // Stay black
            default => EmergencyEscalation::LEVEL_BLACK
        };

        // If already black, escalate further
        if ($currentLevel === EmergencyEscalation::LEVEL_BLACK) {
            // Increment retry and create new escalation if needed
            $escalation->incrementRetry();
            
            if ($escalation->retry_count >= 3) {
                $escalation->expire();
                Log::emergency('Escalation expired after max retries', [
                    'escalation_id' => $escalation->id,
                    'user_id' => $escalation->user_id
                ]);
                return;
            }
        }

        // Update escalation
        $escalation->level = $nextLevel;
        $escalation->reason = "Auto-escalated from {$currentLevel} due to timeout";
        $escalation->save();

        // Re-escalate with new level
        $escalation->escalate();

        // Notify contacts of new escalation level
        dispatch(new SendEscalationNotificationJob($escalation->user_id, $nextLevel));

        // Update confidence score
        $user = User::find($escalation->user_id);
        if ($user) {
            $scoreService = app(SafetyScoreService::class);
            $score = $scoreService->updateForUser($user);
            
            Log::info('Confidence score updated after auto-escalation', [
                'user_id' => $user->id,
                'score' => $score,
                'level' => $nextLevel
            ]);
        }

        Log::warning('Auto-escalation applied', [
            'escalation_id' => $escalation->id,
            'from_level' => $currentLevel,
            'to_level' => $nextLevel,
        ]);
    }
}
