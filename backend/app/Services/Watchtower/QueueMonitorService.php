<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;

class QueueMonitorService
{
    public function getMetrics(): array
    {
        $jobMetrics = $this->getJobMetrics();
        $failedMetrics = $this->getFailedJobMetrics();

        return [
            'jobs' => $jobMetrics,
            'workers' => [
                'driver' => config('queue.default'),
                'is_configured' => config('queue.default') !== 'sync',
                'status' => config('queue.default') !== 'sync' ? 'healthy' : 'warning',
                'workers' => 0,
            ],
            'failed_jobs' => $failedMetrics,
            'status' => $this->getQueueStatus(),
            'overall_health' => $this->calculateOverallHealth($jobMetrics, $failedMetrics),
            'timestamp' => now()->toISOString(),
        ];
    }

    private function getJobMetrics(): array
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
            return ['pending' => 0, 'processing' => 0, 'total' => 0, 'status' => 'unhealthy'];
        }
    }

    private function getFailedJobMetrics(): array
    {
        try {
            if (!DB::connection()->getSchemaBuilder()->hasTable('failed_jobs')) {
                return ['has_table' => false, 'failed_count' => 0, 'status' => 'warning'];
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
            return ['has_table' => false, 'failed_count' => 0, 'status' => 'unhealthy'];
        }
    }

    private function getQueueStatus(): array
    {
        return [
            'driver' => config('queue.default'),
            'is_working' => config('queue.default') !== 'sync',
            'status' => config('queue.default') !== 'sync' ? 'healthy' : 'degraded',
        ];
    }

    private function calculateOverallHealth($jobMetrics, $failedMetrics): array
    {
        $statuses = [$jobMetrics['status'], $failedMetrics['status']];
        $hasCritical = in_array('critical', $statuses);
        $hasUnhealthy = in_array('unhealthy', $statuses);

        if ($hasCritical) { return ['status' => 'critical', 'score' => 30]; }
        if ($hasUnhealthy) { return ['status' => 'warning', 'score' => 60]; }
        return ['status' => 'healthy', 'score' => 100];
    }

    public function checkStuckJobs(): array
    {
        try {
            $stuck = DB::table('jobs')
                ->whereNotNull('reserved_at')
                ->where('reserved_at', '<', now()->subMinutes(10))
                ->count();

            return ['stuck_jobs' => $stuck, 'status' => $stuck > 0 ? 'warning' : 'healthy'];
        } catch (\Exception $e) {
            return ['stuck_jobs' => 0, 'status' => 'unhealthy'];
        }
    }
}
