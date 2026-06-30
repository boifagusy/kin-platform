<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redis;
use App\Models\User;

class HealthService
{
    /**
     * Get overall system health status
     */
    public function getHealthStatus(): array
    {
        return [
            'api' => $this->checkApi(),
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
            'scheduler' => $this->checkScheduler(),
            'disk_usage' => $this->checkDiskUsage(),
            'memory' => $this->checkMemory(),
            'cpu' => $this->checkCpu(),
            'timestamp' => now()->toISOString(),
            'health_score' => $this->calculateHealthScore(),
        ];
    }

    /**
     * Calculate overall health score
     */
    protected function calculateHealthScore(): int
    {
        $checks = [
            $this->checkDatabase()['status'] === 'healthy',
            $this->checkCache()['status'] === 'healthy',
            $this->checkStorage()['status'] === 'healthy',
            $this->checkQueue()['status'] === 'healthy',
        ];

        $healthy = array_filter($checks, function ($check) {
            return $check === true;
        });

        return (int) (count($healthy) / count($checks) * 100);
    }

    /**
     * Check API status
     */
    protected function checkApi(): array
    {
        return [
            'status' => 'healthy',
            'uptime' => $this->getUptime(),
            'response_time' => $this->getResponseTime(),
        ];
    }

    /**
     * Check database status
     */
    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return [
                'status' => 'healthy',
                'connection' => config('database.default'),
                'driver' => DB::connection()->getDriverName(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache status
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'ok', 60);
            $value = Cache::get($key);
            Cache::forget($key);

            return [
                'status' => $value === 'ok' ? 'healthy' : 'degraded',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage status
     */
    protected function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $testFile = 'health_check_' . time() . '.txt';
            $disk->put($testFile, 'ok');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);

            return [
                'status' => $exists ? 'healthy' : 'degraded',
                'driver' => config('filesystems.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue status
     */
    protected function checkQueue(): array
    {
        try {
            // Check if queue table exists
            $hasTable = DB::connection()->getSchemaBuilder()->hasTable('jobs');
            return [
                'status' => $hasTable ? 'healthy' : 'degraded',
                'connection' => config('queue.default'),
                'driver' => config('queue.connections.database.driver'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check scheduler status
     */
    protected function checkScheduler(): array
    {
        // Check if schedule:run has been executed recently
        // This is a simplified check
        try {
            $lastRun = Cache::get('scheduler_last_run');
            $isRecent = $lastRun && now()->diffInMinutes($lastRun) < 10;

            return [
                'status' => $isRecent ? 'healthy' : 'warning',
                'last_run' => $lastRun,
                'is_recent' => $isRecent,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check disk usage
     */
    protected function checkDiskUsage(): array
    {
        try {
            $total = disk_total_space('/');
            $free = disk_free_space('/');
            $used = $total - $free;
            $usedPercentage = round(($used / $total) * 100, 2);

            $status = $usedPercentage < 80 ? 'healthy' : ($usedPercentage < 90 ? 'warning' : 'critical');

            return [
                'status' => $status,
                'total' => $this->formatBytes($total),
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'used_percentage' => $usedPercentage,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check memory usage
     */
    protected function checkMemory(): array
    {
        try {
            $memory = memory_get_usage(true);
            $peak = memory_get_peak_usage(true);
            $limit = $this->getMemoryLimit();

            $percentage = round(($memory / $limit) * 100, 2);
            $status = $percentage < 80 ? 'healthy' : ($percentage < 90 ? 'warning' : 'critical');

            return [
                'status' => $status,
                'current' => $this->formatBytes($memory),
                'peak' => $this->formatBytes($peak),
                'limit' => $this->formatBytes($limit),
                'used_percentage' => $percentage,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check CPU usage
     */
    protected function checkCpu(): array
    {
        try {
            // Simplified CPU check - in production you'd use sys_getloadavg()
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $cpus = $this->getCpuCount();
                $avg = $load[0] / $cpus;

                $status = $avg < 0.7 ? 'healthy' : ($avg < 0.9 ? 'warning' : 'critical');

                return [
                    'status' => $status,
                    'load_1min' => $load[0],
                    'load_5min' => $load[1],
                    'load_15min' => $load[2],
                    'cpus' => $cpus,
                    'avg_per_cpu' => round($avg, 2),
                ];
            }

            return [
                'status' => 'unknown',
                'message' => 'sys_getloadavg not available',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get uptime
     */
    protected function getUptime(): string
    {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime -s 2>/dev/null');
            if ($uptime) {
                return trim($uptime);
            }
        }
        return 'unknown';
    }

    /**
     * Get response time
     */
    protected function getResponseTime(): int
    {
        $start = microtime(true);
        // Simple check - we'll measure actual API response in the controller
        return (int) round((microtime(true) - $start) * 1000);
    }

    /**
     * Get memory limit in bytes
     */
    protected function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        return $this->convertToBytes($limit);
    }

    /**
     * Convert memory limit string to bytes
     */
    protected function convertToBytes(string $limit): int
    {
        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        return $value;
    }

    /**
     * Get CPU count
     */
    protected function getCpuCount(): int
    {
        if (function_exists('shell_exec')) {
            $count = shell_exec('nproc 2>/dev/null');
            if ($count) {
                return (int) trim($count);
            }
        }
        return 1;
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes > 1024 && $i < 4) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
