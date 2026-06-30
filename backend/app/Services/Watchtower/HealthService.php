<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HealthService
{
    public function getHealthStatus(): array
    {
        $diskUsage = $this->checkDiskUsage();
        $database = $this->checkDatabase();
        $cache = $this->checkCache();
        $storage = $this->checkStorage();
        $queue = $this->checkQueue();

        return [
            'api' => ['status' => 'healthy', 'uptime' => $this->getUptime(), 'response_time' => $this->getResponseTime()],
            'database' => $database,
            'cache' => $cache,
            'storage' => $storage,
            'queue' => $queue,
            'scheduler' => ['status' => 'healthy', 'last_run' => 'unknown', 'is_recent' => true],
            'disk_usage' => $diskUsage,
            'memory' => $this->checkMemory(),
            'cpu' => $this->checkCpu(),
            'timestamp' => now()->toISOString(),
            'health_score' => $this->calculateHealthScore([$database, $cache, $storage, $queue, $diskUsage]),
        ];
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'connection' => config('database.default')];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'ok', 60);
            $value = Cache::get($key);
            Cache::forget($key);
            return ['status' => $value === 'ok' ? 'healthy' : 'degraded', 'driver' => config('cache.default')];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function checkStorage(): array
    {
        try {
            $disk = Storage::disk('local');
            $testFile = 'health_check_' . time() . '.txt';
            $disk->put($testFile, 'ok');
            $exists = $disk->exists($testFile);
            $disk->delete($testFile);
            return ['status' => $exists ? 'healthy' : 'degraded', 'driver' => config('filesystems.default')];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $hasTable = DB::connection()->getSchemaBuilder()->hasTable('jobs');
            return ['status' => $hasTable ? 'healthy' : 'degraded', 'connection' => config('queue.default')];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function checkDiskUsage(): array
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
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function checkMemory(): array
    {
        try {
            $memory = memory_get_usage(true);
            $limit = $this->getMemoryLimit();
            $percentage = round(($memory / $limit) * 100, 2);
            $status = $percentage < 80 ? 'healthy' : ($percentage < 90 ? 'warning' : 'critical');

            return ['status' => $status, 'current' => $this->formatBytes($memory), 'limit' => $this->formatBytes($limit), 'used_percentage' => $percentage];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function checkCpu(): array
    {
        try {
            if (function_exists('sys_getloadavg')) {
                $load = sys_getloadavg();
                $cpus = $this->getCpuCount();
                $avg = $load[0] / $cpus;
                $status = $avg < 0.7 ? 'healthy' : ($avg < 0.9 ? 'warning' : 'critical');
                return ['status' => $status, 'load_1min' => $load[0], 'load_5min' => $load[1], 'load_15min' => $load[2], 'cpus' => $cpus];
            }
            return ['status' => 'unknown', 'message' => 'sys_getloadavg not available'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private function getUptime(): string
    {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime -s 2>/dev/null');
            if ($uptime) return trim($uptime);
        }
        return 'unknown';
    }

    private function getResponseTime(): int
    {
        return 0;
    }

    private function calculateHealthScore(array $checks): int
    {
        $healthy = 0;
        $total = 0;
        foreach ($checks as $check) {
            if (isset($check['status'])) {
                $total++;
                if ($check['status'] === 'healthy') $healthy++;
            }
        }
        return $total > 0 ? (int) ($healthy / $total * 100) : 0;
    }

    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') return PHP_INT_MAX;
        return $this->convertToBytes($limit);
    }

    private function convertToBytes(string $limit): int
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

    private function getCpuCount(): int
    {
        if (function_exists('shell_exec')) {
            $count = shell_exec('nproc 2>/dev/null');
            if ($count) return (int) trim($count);
        }
        return 1;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
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
