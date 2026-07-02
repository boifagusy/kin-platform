<?php

namespace App\Services;

use App\Models\User;
use App\Models\DeviceTrust;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DeviceTrustService
{
    /**
     * Get device trust for a user
     */
    public function getDeviceTrust(User $user): ?DeviceTrust
    {
        return DeviceTrust::where('user_id', $user->id)
            ->latest()
            ->first();
    }

    /**
     * Get trust score for a user
     */
    public function getTrustScore(User $user): int
    {
        $deviceTrust = $this->getDeviceTrust($user);
        
        if (!$deviceTrust) {
            return 50; // Default trust score for new devices
        }

        return $deviceTrust->trust_score;
    }

    /**
     * Calculate trust score based on device factors
     */
    public function calculateTrustScore(array $factors): int
    {
        $score = 100;

        // Deduct for security issues
        if ($factors['root_detected'] ?? false) {
            $score -= 30;
        }

        if ($factors['emulator_detected'] ?? false) {
            $score -= 50;
        }

        if ($factors['sim_changed'] ?? false) {
            $score -= 15;
        }

        if ($factors['app_reinstalled'] ?? false) {
            $score -= 20;
        }

        // Age of installation (if known)
        if (isset($factors['installation_days']) && $factors['installation_days'] < 7) {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }

    /**
     * Update device trust for a user
     */
    public function updateDeviceTrust(User $user, array $factors): DeviceTrust
    {
        $fingerprint = $this->generateFingerprint($user, $factors);
        $score = $this->calculateTrustScore($factors);
        $reasons = $this->getTrustReasons($factors);

        $deviceTrust = DeviceTrust::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_fingerprint' => $fingerprint,
            ],
            [
                'trust_score' => $score,
                'root_detected' => $factors['root_detected'] ?? false,
                'emulator_detected' => $factors['emulator_detected'] ?? false,
                'sim_changed' => $factors['sim_changed'] ?? false,
                'app_reinstalled' => $factors['app_reinstalled'] ?? false,
                'reasons' => $reasons,
                'last_checked_at' => now(),
            ]
        );

        Log::info('Device trust updated', [
            'user_id' => $user->id,
            'fingerprint' => $fingerprint,
            'score' => $score,
            'reasons' => $reasons,
        ]);

        // Clear cache
        Cache::forget("device_trust_{$user->id}");

        return $deviceTrust;
    }

    /**
     * Generate device fingerprint
     */
    public function generateFingerprint(User $user, array $factors): string
    {
        $data = [
            'user_id' => $user->id,
            'device_id' => $factors['device_id'] ?? 'unknown',
            'model' => $factors['model'] ?? 'unknown',
            'manufacturer' => $factors['manufacturer'] ?? 'unknown',
            'sdk_version' => $factors['sdk_version'] ?? 'unknown',
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Get trust reasons
     */
    public function getTrustReasons(array $factors): array
    {
        $reasons = [];

        if ($factors['root_detected'] ?? false) {
            $reasons[] = 'Device is rooted or jailbroken';
        }

        if ($factors['emulator_detected'] ?? false) {
            $reasons[] = 'Device is an emulator';
        }

        if ($factors['sim_changed'] ?? false) {
            $reasons[] = 'SIM card was changed';
        }

        if ($factors['app_reinstalled'] ?? false) {
            $reasons[] = 'App was reinstalled';
        }

        if (isset($factors['installation_days']) && $factors['installation_days'] < 7) {
            $reasons[] = 'App installed less than 7 days ago';
        }

        return $reasons;
    }

    /**
     * Check if device is trusted
     */
    public function isDeviceTrusted(User $user): bool
    {
        $score = $this->getTrustScore($user);
        return $score >= 70;
    }

    /**
     * Get device trust breakdown
     */
    public function getBreakdown(User $user): array
    {
        $deviceTrust = $this->getDeviceTrust($user);

        return [
            'trust_score' => $deviceTrust?->trust_score ?? 50,
            'is_trusted' => $deviceTrust?->isTrusted() ?? false,
            'trust_level' => $deviceTrust?->getTrustLevel() ?? 'medium',
            'root_detected' => $deviceTrust?->root_detected ?? false,
            'emulator_detected' => $deviceTrust?->emulator_detected ?? false,
            'sim_changed' => $deviceTrust?->sim_changed ?? false,
            'app_reinstalled' => $deviceTrust?->app_reinstalled ?? false,
            'reasons' => $deviceTrust?->reasons ?? [],
            'last_checked_at' => $deviceTrust?->last_checked_at,
        ];
    }
}
