<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SafetyIncident;
use App\Models\EmergencyEscalation;
use App\Services\SafetyScoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckEscalationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function handle(SafetyScoreService $scoreService): void
    {
        $user = User::find($this->userId);
        if (!$user) return;

        $score = $scoreService->getForUser($user);
        $tier = $scoreService->getTier($score);

        // Check if escalation is needed
        if ($tier === SafetyScoreService::TIER_ORANGE) {
            $this->escalateToOrange($user);
        } elseif ($tier === SafetyScoreService::TIER_RED) {
            $this->escalateToRed($user);
        } elseif ($tier === SafetyScoreService::TIER_BLACK) {
            $this->escalateToBlack($user);
        }
    }

    private function escalateToOrange(User $user): void
    {
        // Create escalation if none exists
        $this->createEscalation($user, 'orange', 'Confidence dropped to Orange tier');
        
        // Notify emergency contacts
        dispatch(new SendEscalationNotificationJob($user->id, 'orange'));
        
        Log::warning('Escalated to Orange', ['user_id' => $user->id]);
    }

    private function escalateToRed(User $user): void
    {
        $this->createEscalation($user, 'red', 'Confidence dropped to Red tier - Immediate attention required');
        
        // Notify all contacts and support
        dispatch(new SendEscalationNotificationJob($user->id, 'red'));
        dispatch(new AlertSupportTeamJob($user->id, 'red'));
        
        Log::alert('Escalated to Red', ['user_id' => $user->id]);
    }

    private function escalateToBlack(User $user): void
    {
        $this->createEscalation($user, 'black', 'EMERGENCY: Confidence at Black tier - Immediate action required');
        
        // Full emergency protocol
        dispatch(new SendEscalationNotificationJob($user->id, 'black'));
        dispatch(new AlertSupportTeamJob($user->id, 'black'));
        dispatch(new ActivateEmergencyProtocolJob($user->id));
        
        Log::emergency('Escalated to Black', ['user_id' => $user->id]);
    }

    private function createEscalation(User $user, string $level, string $reason): void
    {
        $incident = SafetyIncident::firstOrCreate(
            [
                'user_id' => $user->id,
                'status' => 'active',
                'type' => 'escalation'
            ],
            [
                'description' => $reason,
                'confidence_score' => app(SafetyScoreService::class)->getForUser($user),
                'is_duress' => false,
            ]
        );

        EmergencyEscalation::create([
            'safety_incident_id' => $incident->id,
            'status' => 'active',
            'escalation_level' => $level,
            'escalated_at' => now(),
        ]);
    }
}
