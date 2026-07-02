<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerAlertRule;
use Illuminate\Support\Facades\Cache;

class CooldownService
{
    public function shouldSendAlert(string $alertType, string $source): bool
    {
        $key = 'watchtower_cooldown_' . md5($alertType . '_' . $source);
        $lastSent = Cache::get($key);
        
        if (!$lastSent) {
            return true;
        }
        
        // Get cooldown duration from alert rules
        $rule = WatchtowerAlertRule::where('type', $alertType)
            ->where('target', $source)
            ->first();
        
        $cooldownSeconds = $rule->cooldown_seconds ?? 300; // Default 5 minutes
        
        $timeSinceLast = now()->diffInSeconds($lastSent);
        
        if ($timeSinceLast < $cooldownSeconds) {
            // Suppress alert, increment suppressed count
            $this->incrementSuppressedCount($alertType, $source);
            return false;
        }
        
        return true;
    }

    public function recordAlertSent(string $alertType, string $source): void
    {
        $key = 'watchtower_cooldown_' . md5($alertType . '_' . $source);
        Cache::put($key, now(), 3600); // Store for 1 hour
        
        // Reset suppressed count
        $this->resetSuppressedCount($alertType, $source);
    }

    protected function incrementSuppressedCount(string $alertType, string $source): void
    {
        $key = 'watchtower_suppressed_' . md5($alertType . '_' . $source);
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, 3600);
    }

    protected function resetSuppressedCount(string $alertType, string $source): void
    {
        $key = 'watchtower_suppressed_' . md5($alertType . '_' . $source);
        Cache::forget($key);
    }

    public function getSuppressedCount(string $alertType, string $source): int
    {
        $key = 'watchtower_suppressed_' . md5($alertType . '_' . $source);
        return Cache::get($key, 0);
    }
}
