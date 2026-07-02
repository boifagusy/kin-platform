<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\Cache;

class MaintenanceService
{
    public function isMaintenanceMode(): bool
    {
        return Cache::get('watchtower_maintenance_mode', false);
    }

    public function getMaintenanceInfo(): array
    {
        return [
            'active' => $this->isMaintenanceMode(),
            'started_at' => Cache::get('watchtower_maintenance_started_at'),
            'expected_duration' => Cache::get('watchtower_maintenance_duration'),
            'remaining' => $this->getRemainingTime(),
            'affected_services' => Cache::get('watchtower_maintenance_services', []),
            'message' => Cache::get('watchtower_maintenance_message', 'System maintenance in progress'),
        ];
    }

    public function startMaintenance(string $message, int $duration, array $services = []): void
    {
        Cache::put('watchtower_maintenance_mode', true, 86400);
        Cache::put('watchtower_maintenance_started_at', now(), 86400);
        Cache::put('watchtower_maintenance_duration', $duration, 86400);
        Cache::put('watchtower_maintenance_services', $services, 86400);
        Cache::put('watchtower_maintenance_message', $message, 86400);
    }

    public function endMaintenance(): void
    {
        Cache::forget('watchtower_maintenance_mode');
        Cache::forget('watchtower_maintenance_started_at');
        Cache::forget('watchtower_maintenance_duration');
        Cache::forget('watchtower_maintenance_services');
        Cache::forget('watchtower_maintenance_message');
    }

    private function getRemainingTime(): ?int
    {
        $started = Cache::get('watchtower_maintenance_started_at');
        $duration = Cache::get('watchtower_maintenance_duration');
        if (!$started || !$duration) return null;

        $elapsed = now()->diffInSeconds($started);
        return max(0, $duration - $elapsed);
    }
}
