<?php

namespace App\Services;

use App\Models\User;
use App\Models\SafetyIncident;
use App\Models\EmergencyEscalation;
use App\Services\SafetyScoreService;
use App\Services\DeviceTrustService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EscalationDecisionEngine
{
    protected $safetyScoreService;
    protected $deviceTrustService;

    // Decision thresholds
    const THRESHOLD_GREEN = 80;
    const THRESHOLD_YELLOW = 60;
    const THRESHOLD_ORANGE = 40;
    const THRESHOLD_RED = 20;
    const THRESHOLD_BLACK = 0;

    // Cooldown periods (minutes)
    const COOLDOWN_ORANGE = 10;
    const COOLDOWN_RED = 5;
    const COOLDOWN_BLACK = 2;

    public function __construct(
        SafetyScoreService $safetyScoreService,
        DeviceTrustService $deviceTrustService
    ) {
        $this->safetyScoreService = $safetyScoreService;
        $this->deviceTrustService = $deviceTrustService;
    }

    /**
     * Make escalation decision for a user
     */
    public function decide(User $user): DecisionResult
    {
        // Get current state
        $confidence = $this->safetyScoreService->getForUser($user);
        $trustScore = $this->deviceTrustService->getTrustScore($user);
        $tier = $this->safetyScoreService->getTier($confidence);
        $activeIncidents = $this->getActiveIncidents($user);
        
        // Check if already in cooldown
        if ($this->isInCooldown($user)) {
            return new DecisionResult(
                'cooldown',
                $confidence,
                $tier,
                null,
                'In cooldown period',
                false
            );
        }

        // Determine if escalation is needed
        $needsEscalation = $this->needsEscalation($confidence, $tier, $activeIncidents, $trustScore);
        
        if (!$needsEscalation) {
            return new DecisionResult(
                'monitor',
                $confidence,
                $tier,
                null,
                'Normal monitoring',
                false
            );
        }

        // Determine escalation level
        $level = $this->determineLevel($confidence, $tier, $activeIncidents, $trustScore);
        
        // Determine actions
        $actions = $this->determineActions($level, $user);

        // Log the decision
        $this->logDecision($user, $level, $confidence, $actions);

        return new DecisionResult(
            'escalate',
            $confidence,
            $tier,
            $level,
            'Escalation triggered',
            true,
            $actions
        );
    }

    /**
     * Check if escalation is needed
     */
    private function needsEscalation(
        int $confidence,
        string $tier,
        array $activeIncidents,
        int $trustScore
    ): bool {
        // Emergency: Black tier or confidence < 20
        if ($confidence < self::THRESHOLD_RED) {
            return true;
        }

        // Urgent: Red tier or confidence < 40
        if ($confidence < self::THRESHOLD_ORANGE) {
            return true;
        }

        // Warning: Orange tier or confidence < 60
        if ($confidence < self::THRESHOLD_YELLOW) {
            // Check if there are active incidents
            if (count($activeIncidents) > 0) {
                return true;
            }
        }

        // Duress: Check for duress incidents
        $duressIncidents = array_filter($activeIncidents, function ($incident) {
            return $incident->is_duress;
        });
        if (count($duressIncidents) > 0) {
            return true;
        }

        // Device trust: Very low trust
        if ($trustScore < 30) {
            return true;
        }

        return false;
    }

    /**
     * Determine escalation level
     */
    private function determineLevel(
        int $confidence,
        string $tier,
        array $activeIncidents,
        int $trustScore
    ): string {
        // Check for duress
        $duressIncidents = array_filter($activeIncidents, function ($incident) {
            return $incident->is_duress;
        });
        if (count($duressIncidents) > 0) {
            return EmergencyEscalation::LEVEL_RED;
        }

        // Based on confidence
        if ($confidence < self::THRESHOLD_RED) {
            return EmergencyEscalation::LEVEL_BLACK;
        }
        if ($confidence < self::THRESHOLD_ORANGE) {
            return EmergencyEscalation::LEVEL_RED;
        }
        if ($confidence < self::THRESHOLD_YELLOW) {
            return EmergencyEscalation::LEVEL_ORANGE;
        }

        // Multiple incidents
        if (count($activeIncidents) >= 3) {
            return EmergencyEscalation::LEVEL_ORANGE;
        }

        // Very low trust
        if ($trustScore < 30) {
            return EmergencyEscalation::LEVEL_ORANGE;
        }

        return EmergencyEscalation::LEVEL_ORANGE;
    }

    /**
     * Determine actions based on level
     */
    private function determineActions(string $level, User $user): array
    {
        $actions = [];

        switch ($level) {
            case EmergencyEscalation::LEVEL_ORANGE:
                $actions = [
                    'notify_contacts' => true,
                    'send_sms' => true,
                    'send_email' => true,
                    'create_ticket' => true,
                    'escalate_timeout' => 60, // minutes
                ];
                break;

            case EmergencyEscalation::LEVEL_RED:
                $actions = [
                    'notify_contacts' => true,
                    'send_sms' => true,
                    'send_email' => true,
                    'push_notification' => true,
                    'create_ticket' => true,
                    'alert_support' => true,
                    'escalate_timeout' => 30,
                ];
                break;

            case EmergencyEscalation::LEVEL_BLACK:
                $actions = [
                    'notify_contacts' => true,
                    'send_sms' => true,
                    'send_email' => true,
                    'push_notification' => true,
                    'create_ticket' => true,
                    'alert_support' => true,
                    'alert_authorities' => true,
                    'activate_location_tracking' => true,
                    'escalate_timeout' => 15,
                ];
                break;

            default:
                $actions = [
                    'notify_contacts' => false,
                ];
        }

        return $actions;
    }

    /**
     * Get active incidents
     */
    private function getActiveIncidents(User $user): array
    {
        return SafetyIncident::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('created_at', '>', now()->subHours(24))
            ->get()
            ->toArray();
    }

    /**
     * Check if user is in cooldown period
     */
    private function isInCooldown(User $user): bool
    {
        $lastDecision = Cache::get("escalation_decision_{$user->id}");
        if (!$lastDecision) {
            return false;
        }

        $cooldownMinutes = $this->getCooldownForUser($user);
        $elapsed = now()->diffInMinutes($lastDecision['timestamp']);

        return $elapsed < $cooldownMinutes;
    }

    /**
     * Get cooldown for user based on last level
     */
    private function getCooldownForUser(User $user): int
    {
        $lastLevel = Cache::get("escalation_last_level_{$user->id}", 'green');
        
        return match($lastLevel) {
            EmergencyEscalation::LEVEL_ORANGE => self::COOLDOWN_ORANGE,
            EmergencyEscalation::LEVEL_RED => self::COOLDOWN_RED,
            EmergencyEscalation::LEVEL_BLACK => self::COOLDOWN_BLACK,
            default => 5
        };
    }

    /**
     * Log decision
     */
    private function logDecision(User $user, string $level, int $confidence, array $actions): void
    {
        Log::info('Escalation decision made', [
            'user_id' => $user->id,
            'user_phone' => $user->phone,
            'level' => $level,
            'confidence' => $confidence,
            'actions' => $actions,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Cache the decision for cooldown
        Cache::put("escalation_decision_{$user->id}", [
            'timestamp' => now(),
            'level' => $level,
            'confidence' => $confidence,
        ], 3600);

        Cache::put("escalation_last_level_{$user->id}", $level, 3600);
    }

    /**
     * Get decision history for a user
     */
    public function getDecisionHistory(User $user, int $limit = 10): array
    {
        // This would pull from a decision_logs table
        // For now, return empty array
        return [];
    }

    /**
     * Reset cooldown for a user (admin override)
     */
    public function resetCooldown(User $user): void
    {
        Cache::forget("escalation_decision_{$user->id}");
        Cache::forget("escalation_last_level_{$user->id}");
        
        Log::info('Escalation cooldown reset', [
            'user_id' => $user->id,
        ]);
    }
}

/**
 * Decision Result Object
 */
class DecisionResult
{
    public function __construct(
        public string $action,
        public int $confidence,
        public string $tier,
        public ?string $level = null,
        public string $reason = '',
        public bool $escalated = false,
        public array $actions = []
    ) {}

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'confidence' => $this->confidence,
            'tier' => $this->tier,
            'level' => $this->level,
            'reason' => $this->reason,
            'escalated' => $this->escalated,
            'actions' => $this->actions,
        ];
    }
}
