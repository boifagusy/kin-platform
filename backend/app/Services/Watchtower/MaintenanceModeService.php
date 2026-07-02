<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\Cache;

class MaintenanceModeService
{
    public function isMaintenanceMode(): bool
    {
        return Cache::get('watchtower_maintenance_mode', false);
    }

    public function enable(string $reason = 'Scheduled maintenance'): void
    {
        Cache::put('watchtower_maintenance_mode', true, 86400); // 24 hours
        Cache::put('watchtower_maintenance_reason', $reason, 86400);
        Cache::put('watchtower_maintenance_started_at', now()->toISOString(), 86400);
    }

    public function disable(): void
    {
        Cache::forget('watchtower_maintenance_mode');
        Cache::forget('watchtower_maintenance_reason');
        Cache::forget('watchtower_maintenance_started_at');
    }

    public function getMaintenanceInfo(): array
    {
        return [
            'is_enabled' => $this->isMaintenanceMode(),
            'reason' => Cache::get('watchtower_maintenance_reason', 'No reason provided'),
            'started_at' => Cache::get('watchtower_maintenance_started_at'),
        ];
    }

    public function shouldSuppressAlert(string $alertType): bool
    {
        if (!$this->isMaintenanceMode()) {
            return false;
        }

        // During maintenance, suppress all alerts except critical
        $criticalAlerts = ['storage_critical', 'plugin_offline', 'security_breach'];
        
        return !in_array($alertType, $criticalAlerts);
    }
}
