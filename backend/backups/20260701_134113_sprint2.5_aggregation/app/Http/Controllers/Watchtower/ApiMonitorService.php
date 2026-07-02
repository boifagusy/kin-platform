<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiMonitorService
{
    private $metrics = [];
    private $thresholds = [
        'response_time_warning' => 500,   // ms
        'response_time_critical' => 1000, // ms
        'error_rate_warning' => 5,        // %
        'error_rate_critical' => 10,      // %
    ];

    /**
     * Get API performance metrics
     */
    public function getMetrics(): array
    {
        return [
            'response_times' => $this->getResponseTimes(),
            'error_rates' => $this->getErrorRates(),
            'top_endpoints' => $this->getTopEndpoints(),
            'slow_endpoints' => $this->getSlowEndpoints(),
            'status_distribution' => $this->getStatusDistribution(),
            'overall_health' => $this->calculateOverallHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get response time metrics
     */
    protected function getResponseTimes(): array
    {
        // In production, this would read from actual request logs
        // For now, we'll return sample data structure
        return [
            'p50' => 120,
            'p90' => 350,
            'p95' => 520,
            'p99' => 1200,
            'average' => 280,
            'max' => 3000,
            'min' => 10,
        ];
    }

    /**
     * Get error rates
     */
    protected function getErrorRates(): array
    {
        return [
            '4xx_rate' => 2.5,
            '5xx_rate' => 0.8,
            'total_errors' => 125,
            'total_requests' => 15000,
            'error_rate' => 3.3,
        ];
    }

    /**
     * Get top endpoints
     */
    protected function getTopEndpoints(): array
    {
        // In production, this would be from actual request logs
        return [
            [
                'endpoint' => '/api/v1/dashboard',
                'method' => 'GET',
                'requests' => 8500,
                'avg_response' => 250,
            ],
            [
                'endpoint' => '/api/v1/checkin',
                'method' => 'POST',
                'requests' => 3200,
                'avg_response' => 350,
            ],
            [
                'endpoint' => '/api/v1/sos',
                'method' => 'POST',
                'requests' => 150,
                'avg_response' => 450,
            ],
            [
                'endpoint' => '/api/v1/safe-zones',
                'method' => 'GET',
                'requests' => 1200,
                'avg_response' => 200,
            ],
        ];
    }

    /**
     * Get slow endpoints
     */
    protected function getSlowEndpoints(): array
    {
        return [
            [
                'endpoint' => '/api/v1/sos',
                'method' => 'POST',
                'avg_response' => 1200,
                'requests' => 150,
            ],
            [
                'endpoint' => '/api/v1/dashboard',
                'method' => 'GET',
                'avg_response' => 850,
                'requests' => 8500,
            ],
        ];
    }

    /**
     * Get status distribution
     */
    protected function getStatusDistribution(): array
    {
        return [
            '2xx' => 85.0,
            '3xx' => 5.0,
            '4xx' => 7.0,
            '5xx' => 3.0,
        ];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $errorRate = $this->getErrorRates()['error_rate'] ?? 0;
        $avgResponse = $this->getResponseTimes()['average'] ?? 0;

        $status = 'healthy';
        $score = 100;

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

        // Check response time
        $avgResponse = $this->getResponseTimes()['average'] ?? 0;
        if ($avgResponse > $this->thresholds['response_time_warning']) {
            $degradations[] = [
                'type' => 'response_time',
                'severity' => $avgResponse > $this->thresholds['response_time_critical'] ? 'critical' : 'warning',
                'message' => "Average response time is {$avgResponse}ms",
                'threshold' => $this->thresholds['response_time_warning'],
            ];
        }

        // Check error rate
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
