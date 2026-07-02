<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ErrorMonitorService
{
    /**
     * Get error metrics
     */
    public function getMetrics(): array
    {
        return [
            'laravel_errors' => $this->getLaravelErrors(),
            'api_errors' => $this->getApiErrors(),
            'network_errors' => $this->getNetworkErrors(),
            'offline_failures' => $this->getOfflineFailures(),
            'plugin_errors' => $this->getPluginErrors(),
            'overall_health' => $this->calculateOverallHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get Laravel error metrics
     */
    protected function getLaravelErrors(): array
    {
        try {
            // Check if error logs exist
            $logPath = storage_path('logs/laravel.log');
            $errorCount = 0;

            if (file_exists($logPath)) {
                $content = file_get_contents($logPath);
                $errorCount = substr_count($content, 'ERROR') + substr_count($content, 'CRITICAL');
            }

            return [
                'error_count' => $errorCount,
                'log_size' => file_exists($logPath) ? round(filesize($logPath) / 1024, 2) . 'KB' : '0KB',
                'status' => $errorCount < 10 ? 'healthy' : 'warning',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get API error metrics
     */
    protected function getApiErrors(): array
    {
        // In production, this would read from API logs
        return [
            '4xx_errors' => 125,
            '5xx_errors' => 15,
            'total_errors' => 140,
            'status' => 15 < 10 ? 'healthy' : 'warning',
        ];
    }

    /**
     * Get network error metrics
     */
    protected function getNetworkErrors(): array
    {
        // In production, this would track network failures
        return [
            'failed_requests' => 45,
            'timeouts' => 12,
            'status' => 12 < 5 ? 'warning' : 'healthy',
        ];
    }

    /**
     * Get offline failure metrics
     */
    protected function getOfflineFailures(): array
    {
        // In production, this would track offline queue failures
        return [
            'failed_syncs' => 8,
            'pending_offline' => 3,
            'status' => 8 < 10 ? 'healthy' : 'warning',
        ];
    }

    /**
     * Get plugin error metrics
     */
    protected function getPluginErrors(): array
    {
        // In production, this would check plugin error logs
        return [
            'total_errors' => 5,
            'latest_error' => null,
            'status' => 5 < 10 ? 'healthy' : 'warning',
        ];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $statuses = [
            $this->getLaravelErrors()['status'] ?? 'healthy',
            $this->getApiErrors()['status'] ?? 'healthy',
            $this->getNetworkErrors()['status'] ?? 'healthy',
            $this->getOfflineFailures()['status'] ?? 'healthy',
            $this->getPluginErrors()['status'] ?? 'healthy',
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
}
