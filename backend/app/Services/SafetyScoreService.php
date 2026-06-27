<?php

namespace App\Services;

use App\Models\User;
use App\Models\CheckIn;
use App\Models\CheckinSetting;
use App\Models\SafetyIncident;
use App\Models\TrustedContact;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SafetyScoreService
{
    // Escalation Tiers
    const TIER_GREEN = 'green';
    const TIER_YELLOW = 'yellow';
    const TIER_ORANGE = 'orange';
    const TIER_RED = 'red';
    const TIER_BLACK = 'black';

    // Factor Weights (sum = 1.0)
    const WEIGHT_CHECKIN = 0.35;
    const WEIGHT_CONTACTS = 0.15;
    const WEIGHT_DURESS = 0.20;
    const WEIGHT_HISTORY = 0.10;
    const WEIGHT_ACTIVITY = 0.10;
    const WEIGHT_DEVICE_TRUST = 0.10;

    /**
     * Get safety score for a user (cached)
     */
    public function getForUser(User $user): int
    {
        return Cache::remember("safety_score_{$user->id}", 300, function () use ($user) {
            return $this->calculate($user);
        });
    }

    /**
     * Update safety score for a user
     */
    public function updateForUser(User $user): int
    {
        $score = $this->calculate($user);
        Cache::put("safety_score_{$user->id}", $score, 300);
        
        // Log the update
        Log::info('Safety score updated', [
            'user_id' => $user->id,
            'score' => $score,
            'tier' => $this->getTier($score)
        ]);
        
        return $score;
    }

    /**
     * Get escalation tier
     */
    public function getTier(int $score): string
    {
        if ($score >= 80) return self::TIER_GREEN;
        if ($score >= 60) return self::TIER_YELLOW;
        if ($score >= 40) return self::TIER_ORANGE;
        if ($score >= 20) return self::TIER_RED;
        return self::TIER_BLACK;
    }

    /**
     * Get tier color
     */
    public function getTierColor(string $tier): string
    {
        return [
            self::TIER_GREEN => '#22c55e',
            self::TIER_YELLOW => '#eab308',
            self::TIER_ORANGE => '#f97316',
            self::TIER_RED => '#ef4444',
            self::TIER_BLACK => '#1a1a1a',
        ][$tier] ?? '#94a3b8';
    }

    /**
     * Calculate confidence score (0-100)
     */
    private function calculate(User $user): int
    {
        $factors = $this->getFactors($user);
        $score = $this->weightedSum($factors);
        $penalty = $this->calculatePenalty($user);
        
        $finalScore = max(0, min(100, $score - $penalty));
        
        return (int) $finalScore;
    }

    /**
     * Get all factors for a user
     */
    private function getFactors(User $user): array
    {
        return [
            'checkin' => $this->getCheckInFactor($user),
            'contacts' => $this->getContactsFactor($user),
            'duress' => $this->getDuressFactor($user),
            'history' => $this->getHistoryFactor($user),
            'activity' => $this->getActivityFactor($user),
            'device_trust' => $this->getDeviceTrustFactor($user),
        ];
    }

    /**
     * Weighted sum calculation
     */
    private function weightedSum(array $factors): float
    {
        $weights = [
            'checkin' => self::WEIGHT_CHECKIN,
            'contacts' => self::WEIGHT_CONTACTS,
            'duress' => self::WEIGHT_DURESS,
            'history' => self::WEIGHT_HISTORY,
            'activity' => self::WEIGHT_ACTIVITY,
            'device_trust' => self::WEIGHT_DEVICE_TRUST,
        ];

        $total = 0;
        foreach ($factors as $key => $value) {
            $total += ($weights[$key] ?? 0) * $value;
        }

        return $total * 100;
    }

    /**
     * Calculate penalty
     */
    private function calculatePenalty(User $user): int
    {
        $penalty = 0;

        // Recent duress incidents (last 24 hours)
        $duressIncidents = SafetyIncident::where('user_id', $user->id)
            ->where('is_duress', true)
            ->where('created_at', '>', now()->subHours(24))
            ->count();
        $penalty += $duressIncidents * 15;

        // Recent safety incidents (last 24 hours)
        $incidents = SafetyIncident::where('user_id', $user->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();
        $penalty += $incidents * 10;

        // Missed check-ins (last 24 hours)
        $missedCheckins = CheckIn::where('user_id', $user->id)
            ->where('status', 'missed')
            ->where('created_at', '>', now()->subHours(24))
            ->count();
        $penalty += $missedCheckins * 5;

        return min($penalty, 50);
    }

    /**
     * Check-in factor (0-1)
     */
    private function getCheckInFactor(User $user): float
    {
        $lastCheckIn = CheckIn::where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest()
            ->first();

        if (!$lastCheckIn) {
            return 0.2;
        }

        $hoursAgo = $lastCheckIn->created_at->diffInHours(now());

        if ($hoursAgo < 4) return 1.0;
        if ($hoursAgo < 8) return 0.8;
        if ($hoursAgo < 12) return 0.6;
        if ($hoursAgo < 24) return 0.4;
        return 0.2;
    }

    /**
     * Contacts factor (0-1)
     */
    private function getContactsFactor(User $user): float
    {
        $count = TrustedContact::where('user_id', $user->id)
            ->where('verified', true)
            ->count();

        if ($count >= 3) return 1.0;
        if ($count >= 2) return 0.7;
        if ($count >= 1) return 0.4;
        return 0.0;
    }

    /**
     * Duress factor (0-1)
     */
    private function getDuressFactor(User $user): float
    {
        if (!empty($user->duress_pin_hash)) {
            return 1.0;
        }
        return 0.3;
    }

    /**
     * History factor (0-1)
     */
    private function getHistoryFactor(User $user): float
    {
        // Check if user has consistent check-in history
        $lastWeek = CheckIn::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDays(7))
            ->where('status', 'completed')
            ->count();

        $expected = 7; // One per day
        $ratio = min($lastWeek / $expected, 1.0);
        
        return $ratio * 0.8 + 0.2; // Minimum 0.2
    }

    /**
     * Activity factor (0-1)
     */
    private function getActivityFactor(User $user): float
    {
        // Check recent activity (last 24 hours)
        $recentActivity = SafetyIncident::where('user_id', $user->id)
            ->where('created_at', '>', now()->subHours(24))
            ->count();

        if ($recentActivity > 0) {
            return 0.6; // Some activity but could be concerning
        }

        return 0.9;
    }

    /**
     * Device trust factor (0-1)
     */
    private function getDeviceTrustFactor(User $user): float
    {
        // This will be filled when DeviceTrustService is built
        // For now, return a default
        return 0.8;
    }

    /**
     * Get confidence score breakdown for debugging
     */
    public function getBreakdown(User $user): array
    {
        $factors = $this->getFactors($user);
        $score = $this->weightedSum($factors);
        $penalty = $this->calculatePenalty($user);
        $finalScore = max(0, min(100, $score - $penalty));

        return [
            'factors' => $factors,
            'weighted_score' => round($score, 2),
            'penalty' => $penalty,
            'final_score' => (int) $finalScore,
            'tier' => $this->getTier((int) $finalScore),
        ];
    }
}
