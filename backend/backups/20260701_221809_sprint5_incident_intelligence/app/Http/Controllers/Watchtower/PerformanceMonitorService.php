<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\Cache;

class PerformanceMonitorService
{
    /**
     * Get performance metrics
     */
    public function getMetrics(): array
    {
        return [
            'system' => $this->getSystemMetrics(),
            'memory' => $this->getMemoryMetrics(),
            'storage' => $this->getStorageMetrics(),
            'api_latency' => $this->getApiLatencyMetrics(),
            'overall_health' => $this->calculateOverallHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get system performance metrics
     */
    protected function getSystemMetrics(): array
    {
        try {
            $load = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];
            $cpus = $this->getCpuCount();

            return [
                'cpu_usage' => round(($load[0] / $cpus) * 100, 2),
                'load_1min' => $load[0],
                'load_5min' => $load[1],
                'load_15min' => $load[2],
                'cpus' => $cpus,
                'status' => $load[0] < $cpus * 0.8 ? 'healthy' : 'warning',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get memory metrics
     */
    protected function getMemoryMetrics(): array
    {
        try {
            $memory = memory_get_usage(true);
            $peak = memory_get_peak_usage(true);
            $limit = $this->getMemoryLimit();

            return [
                'used' => $this->formatBytes($memory),
                'peak' => $this->formatBytes($peak),
                'limit' => $this->formatBytes($limit),
                'used_percentage' => round(($memory / $limit) * 100, 2),
                'status' => ($memory / $limit) < 0.8 ? 'healthy' : 'warning',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage metrics
     */
    protected function getStorageMetrics(): array
    {
        try {
            $total = disk_total_space('/');
            $free = disk_free_space('/');
            $used = $total - $free;

            return [
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'total' => $this->formatBytes($total),
                'used_percentage' => round(($used / $total) * 100, 2),
                'status' => ($used / $total) < 0.85 ? 'healthy' : 'warning',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get API latency metrics
     */
    protected function getApiLatencyMetrics(): array
    {
        // In production, this would measure actual API response times
        // For now, return simulated values
        return [
            'average' => 280,
            'p95' => 520,
            'p99' => 1200,
            'status' => 280 < 500 ? 'healthy' : 'warning',
        ];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $statuses = [
            $this->getSystemMetrics()['status'] ?? 'healthy',
            $this->getMemoryMetrics()['status'] ?? 'healthy',
            $this->getStorageMetrics()['status'] ?? 'healthy',
            $this->getApiLatencyMetrics()['status'] ?? 'healthy',
        ];

        $critical = array_filter($statuses, function ($status) {
            return $status === 'critical';
        });

        $unhealthy = array_filter($statuses, function ($status) {
            return $status === 'unhealthy';
        });

        if (count($critical) > 0) {
            $status = 'critical';
            $score = 30;
        } elseif (count($unhealthy) > 0) {
            $status = 'warning';
            $score = 60;
        } else {
            $status = 'healthy';
            $score = 100;
        }

        return [
            'status' => $status,
            'score' => $score,
        ];
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
     * Get memory limit
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
     * Convert memory limit to bytes
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
