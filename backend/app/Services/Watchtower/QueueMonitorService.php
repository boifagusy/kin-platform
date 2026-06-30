<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class QueueMonitorService
{
    /**
     * Get queue metrics
     */
    public function getMetrics(): array
    {
        return [
            'jobs' => $this->getJobMetrics(),
            'workers' => $this->getWorkerMetrics(),
            'failed_jobs' => $this->getFailedJobMetrics(),
            'status' => $this->getQueueStatus(),
            'overall_health' => $this->calculateOverallHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get job metrics
     */
    protected function getJobMetrics(): array
    {
        try {
            $pending = DB::table('jobs')->whereNull('reserved_at')->count();
            $processing = DB::table('jobs')->whereNotNull('reserved_at')->count();

            return [
                'pending' => $pending,
                'processing' => $processing,
                'total' => $pending + $processing,
                'status' => $pending > 100 ? 'warning' : 'healthy',
            ];
        } catch (\Exception $e) {
            return [
                'pending' => 0,
                'processing' => 0,
                'total' => 0,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get worker metrics
     */
    protected function getWorkerMetrics(): array
    {
        // In production, you'd check actual running workers
        // For now, we'll check if the queue system is configured
        $driver = config('queue.default', 'sync');
        $isConfigured = $driver !== 'sync';

        return [
            'driver' => $driver,
            'is_configured' => $isConfigured,
            'status' => $isConfigured ? 'healthy' : 'warning',
            'workers' => $isConfigured ? 1 : 0,
        ];
    }

    /**
     * Get failed job metrics
     */
    protected function getFailedJobMetrics(): array
    {
        try {
            $hasTable = DB::connection()->getSchemaBuilder()->hasTable('failed_jobs');

            if (!$hasTable) {
                return [
                    'has_table' => false,
                    'failed_count' => 0,
                    'status' => 'warning',
                    'message' => 'failed_jobs table not found',
                ];
            }

            $count = DB::table('failed_jobs')->count();
            $recent = DB::table('failed_jobs')->where('failed_at', '>', now()->subHours(24))->count();

            return [
                'has_table' => true,
                'failed_count' => $count,
                'failed_last_24h' => $recent,
                'status' => $count > 10 ? 'critical' : ($count > 5 ? 'warning' : 'healthy'),
            ];
        } catch (\Exception $e) {
            return [
                'has_table' => false,
                'failed_count' => 0,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue status
     */
    protected function getQueueStatus(): array
    {
        $driver = config('queue.default');
        $isWorking = $driver !== 'sync';

        return [
            'driver' => $driver,
            'is_working' => $isWorking,
            'status' => $isWorking ? 'healthy' : 'degraded',
        ];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $statuses = [
            $this->getJobMetrics()['status'],
            $this->getWorkerMetrics()['status'],
            $this->getFailedJobMetrics()['status'],
        ];

        $healthy = array_filter($statuses, function ($status) {
            return $status === 'healthy';
        });

        $critical = array_filter($statuses, function ($status) {
            return $status === 'critical';
        });

        if (count($critical) > 0) {
            $status = 'critical';
            $score = 30;
        } elseif (count($healthy) < count($statuses)) {
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
     * Check for stuck jobs
     */
    public function checkStuckJobs(): array
    {
        try {
            $stuckJobs = DB::table('jobs')
                ->whereNotNull('reserved_at')
                ->where('reserved_at', '<', now()->subMinutes(10))
                ->count();

            return [
                'stuck_jobs' => $stuckJobs,
                'status' => $stuckJobs > 0 ? 'warning' : 'healthy',
            ];
        } catch (\Exception $e) {
            return [
                'stuck_jobs' => 0,
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
}
