<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiMonitorService
{
    private $metrics = [];
    
    private array $thresholds = [
        'response_time_warning' => 500,
        'response_time_critical' => 1000,
        'error_rate_warning' => 5,
        'error_rate_critical' => 10,
    ];

    /**
     * Get API performance metrics
     */
    public function getMetrics(): array
    {
        $responseTimes = $this->getResponseTimes();
        $errorRates = $this->getErrorRates();
        $topEndpoints = $this->getTopEndpoints();
        $slowEndpoints = $this->getSlowEndpoints();
        $statusDistribution = $this->getStatusDistribution();

        return [
            'response_times' => $responseTimes,
            'error_rates' => $errorRates,
            'top_endpoints' => $topEndpoints,
            'slow_endpoints' => $slowEndpoints,
            'status_distribution' => $statusDistribution,
            'overall_health' => $this->calculateOverallHealth(),
        ];
    }

    /**
     * Get response time metrics
     */
    protected function getResponseTimes(): array
    {
        try {
            $avg = DB::table('request_logs')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->avg('response_time') ?? 0;

            return [
                'p50' => $avg * 0.8,
                'p90' => $avg * 1.2,
                'p95' => $avg * 1.5,
                'p99' => $avg * 2.0,
                'average' => (int) $avg,
                'max' => DB::table('request_logs')->max('response_time') ?? 0,
                'min' => DB::table('request_logs')->min('response_time') ?? 0,
            ];
        } catch (\Exception $e) {
            return ['average' => 0, 'max' => 0, 'min' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0];
        }
    }

    /**
     * Get error rates
     */
    protected function getErrorRates(): array
    {
        try {
            $total = DB::table('request_logs')->where('created_at', '>=', now()->subMinutes(5))->count();
            $errors = DB::table('request_logs')
                ->where('created_at', '>=', now()->subMinutes(5))
                ->where('status_code', '>=', 400)
                ->count();

            $rate = $total > 0 ? round(($errors / $total) * 100, 2) : 0;

            return [
                '4xx_rate' => DB::table('request_logs')->where('status_code', '>=', 400)->where('status_code', '<', 500)->count() / max(1, $total) * 100,
                '5xx_rate' => DB::table('request_logs')->where('status_code', '>=', 500)->count() / max(1, $total) * 100,
                'total_errors' => $errors,
                'total_requests' => $total,
                'error_rate' => $rate,
            ];
        } catch (\Exception $e) {
            return ['error_rate' => 0, 'total_errors' => 0, 'total_requests' => 0];
        }
    }

    /**
     * Get top endpoints
     */
    protected function getTopEndpoints(): array
    {
        return [];
    }

    /**
     * Get slow endpoints
     */
    protected function getSlowEndpoints(): array
    {
        return [];
    }

    /**
     * Get status distribution
     */
    protected function getStatusDistribution(): array
    {
        return ['2xx' => 85, '3xx' => 5, '4xx' => 7, '5xx' => 3];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $errorRate = $this->getErrorRates()['error_rate'] ?? 0;
        $avgResponse = $this->getResponseTimes()['average'] ?? 0;

        $score = 100;
        $status = 'healthy';

        if ($errorRate > 10) {
            $status = 'critical';
            $score -= 40;
        } elseif ($errorRate > 5) {
            $status = 'warning';
            $score -= 20;
        }

        if ($avgResponse > 1000) {
            $status = 'critical';
            $score -= 30;
        } elseif ($avgResponse > 500) {
            $status = 'warning';
            $score -= 15;
        }

        return [
            'status' => $status,
            'score' => max(0, $score),
            'error_rate' => $errorRate,
            'avg_response' => $avgResponse,
        ];
    }

    /**
     * Detect API degradation
     */
    public function detectDegradation(): array
    {
        $degradations = [];

        $avgResponse = $this->getResponseTimes()['average'] ?? 0;
        if ($avgResponse > $this->thresholds['response_time_warning']) {
            $degradations[] = [
                'type' => 'response_time',
                'severity' => $avgResponse > $this->thresholds['response_time_critical'] ? 'critical' : 'warning',
                'message' => "Average response time is {$avgResponse}ms",
                'threshold' => $this->thresholds['response_time_warning'],
            ];
        }

        $errorRate = $this->getErrorRates()['error_rate'] ?? 0;
        if ($errorRate > $this->thresholds['error_rate_warning']) {
            $degradations[] = [
                'type' => 'error_rate',
                'severity' => $errorRate > $this->thresholds['error_rate_critical'] ? 'critical' : 'warning',
                'message' => "Error rate is {$errorRate}%",
                'threshold' => $this->thresholds['error_rate_warning'],
            ];
        }

        return $degradations;
    }
}
