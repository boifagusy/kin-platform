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
            'queue_status' => $this->getQueueStatus(),
            'overall_health' => $this->calculateOverallHealth(),
        ];
    }

    /**
     * Get job metrics
     */
    protected function getJobMetrics(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $reserved = DB::table('jobs')->whereNotNull('reserved_at')->count();

            return [
                'pending' => $pending - $reserved,
                'processing' => $reserved,
                'total' => $pending,
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

            $failedCount = DB::table('failed_jobs')->count();

            return [
                'has_table' => true,
                'failed_count' => $failedCount,
                'status' => $failedCount > 10 ? 'warning' : 'healthy',
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
                ->where('created_at', '<', now()->subHours(1))
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
