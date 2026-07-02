<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WatchtowerHealthService
{
    private $startTime;

    public function __construct()
    {
        $this->startTime = Cache::get('watchtower_start_time', microtime(true));
        Cache::put('watchtower_start_time', $this->startTime);
    }

    public function getHealth(): array
    {
        return [
            'status' => $this->isHealthy(),
            'version' => '1.1.0',
            'start_time' => date('Y-m-d H:i:s', (int) $this->startTime),
            'uptime' => $this->getUptime(),
            'last_scan' => $this->getLastScan(),
            'scan_count' => $this->getScanCount(),
            'errors' => $this->getErrorCount(),
            'warnings' => $this->getWarningCount(),
            'memory_usage' => memory_get_usage(true),
            'cpu_usage' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 0,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function isHealthy(): string
    {
        try {
            DB::connection()->getPdo();
            return 'healthy';
        } catch (\Exception $e) {
            return 'degraded';
        }
    }

    private function getUptime(): string
    {
        $uptime = microtime(true) - $this->startTime;
        $hours = floor($uptime / 3600);
        $minutes = floor(($uptime % 3600) / 60);
        $seconds = floor($uptime % 60);
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    private function getLastScan(): ?string
    {
        return Cache::get('watchtower_last_scan');
    }

    private function getScanCount(): int
    {
        return Cache::get('watchtower_scan_count', 0);
    }

    private function getErrorCount(): int
    {
        return Cache::get('watchtower_error_count', 0);
    }

    private function getWarningCount(): int
    {
        return Cache::get('watchtower_warning_count', 0);
    }
}
