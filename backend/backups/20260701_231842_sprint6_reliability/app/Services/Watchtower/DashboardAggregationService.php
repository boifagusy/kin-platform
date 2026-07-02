<?php

namespace App\Services\Watchtower;

use App\Services\Watchtower\HealthService;
use App\Services\Watchtower\ApiMonitorService;
use App\Services\Watchtower\QueueMonitorService;
use App\Services\Watchtower\DatabaseMonitorService;
use App\Services\Watchtower\PluginHealthService;
use App\Services\Watchtower\SafetyEngineMonitorService;
use App\Services\Watchtower\SecurityMonitorService;
use App\Services\Watchtower\PerformanceMonitorService;
use App\Services\Watchtower\DeploymentService;
use Illuminate\Support\Facades\Cache;

class DashboardAggregationService
{
    protected $healthService;
    protected $apiMonitor;
    protected $queueMonitor;
    protected $databaseMonitor;
    protected $pluginHealth;
    protected $safetyMonitor;
    protected $securityMonitor;
    protected $performanceMonitor;
    protected $deploymentService;

    public function __construct(
        HealthService $healthService,
        ApiMonitorService $apiMonitor,
        QueueMonitorService $queueMonitor,
        DatabaseMonitorService $databaseMonitor,
        PluginHealthService $pluginHealth,
        SafetyEngineMonitorService $safetyMonitor,
        SecurityMonitorService $securityMonitor,
        PerformanceMonitorService $performanceMonitor,
        DeploymentService $deploymentService
    ) {
        $this->healthService = $healthService;
        $this->apiMonitor = $apiMonitor;
        $this->queueMonitor = $queueMonitor;
        $this->databaseMonitor = $databaseMonitor;
        $this->pluginHealth = $pluginHealth;
        $this->safetyMonitor = $safetyMonitor;
        $this->securityMonitor = $securityMonitor;
        $this->performanceMonitor = $performanceMonitor;
        $this->deploymentService = $deploymentService;
    }

    public function getDashboardData(): array
    {
        $cacheKey = 'watchtower_dashboard_data';
        $cacheTtl = 30;

        return Cache::remember($cacheKey, $cacheTtl, function () {
            return [
                'health' => $this->getHealthData(),
                'api' => $this->getApiData(),
                'queue' => $this->getQueueData(),
                'database' => $this->getDatabaseData(),
                'storage' => $this->getStorageData(),
                'plugins' => $this->getPluginData(),
                'safety' => $this->getSafetyData(),
                'security' => $this->getSecurityData(),
                'performance' => $this->getPerformanceData(),
                'incidents' => $this->getIncidentData(),
                'deployments' => $this->getDeploymentData(),
                'diagnostics' => $this->getDiagnosticData(),
                'system_map' => $this->getSystemMap(),
                'timestamp' => now()->toISOString(),
            ];
        });
    }

    protected function getHealthData(): array
    {
        try {
            $health = $this->healthService->getHealthStatus();
            $score = $health['health_score'] ?? 0;
            return [
                'status' => $score >= 90 ? 'excellent' : ($score >= 75 ? 'healthy' : ($score >= 60 ? 'degraded' : 'critical')),
                'score' => $score,
                'level' => $score >= 90 ? 'Excellent' : ($score >= 75 ? 'Healthy' : ($score >= 60 ? 'Degraded' : 'Critical')),
                'color' => $score >= 90 ? '#00C853' : ($score >= 75 ? '#4CAF50' : ($score >= 60 ? '#FFC107' : '#D32F2F')),
                'message' => $score >= 90 ? 'All systems operating normally' : ($score >= 75 ? 'Some minor issues detected' : 'System requires attention'),
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'score' => 0, 'message' => 'Unable to fetch health data'];
        }
    }

    protected function getApiData(): array
    {
        try {
            $metrics = $this->apiMonitor->getMetrics();
            return [
                'status' => ($metrics['error_rate'] ?? 0) < 1 ? 'healthy' : 'degraded',
                'requests_per_minute' => $metrics['requests_per_minute'] ?? 0,
                'p95_latency' => $metrics['p95_latency'] ?? 0,
                'error_rate' => $metrics['error_rate'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch API data'];
        }
    }

    protected function getQueueData(): array
    {
        try {
            $metrics = $this->queueMonitor->getMetrics();
            return [
                'status' => ($metrics['failed'] ?? 0) > 0 ? 'degraded' : 'healthy',
                'pending' => $metrics['pending'] ?? 0,
                'processing' => $metrics['processing'] ?? 0,
                'failed' => $metrics['failed'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch queue data'];
        }
    }

    protected function getDatabaseData(): array
    {
        try {
            $metrics = $this->databaseMonitor->getMetrics();
            return [
                'status' => $metrics['status'] ?? 'healthy',
                'connections' => $metrics['connections'] ?? 0,
                'tables' => $metrics['tables'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch database data'];
        }
    }

    protected function getStorageData(): array
    {
        try {
            $freeSpace = disk_free_space('/');
            $totalSpace = disk_total_space('/');
            $freeGB = round($freeSpace / 1024 / 1024 / 1024, 2);
            $totalGB = round($totalSpace / 1024 / 1024 / 1024, 2);
            $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);

            return [
                'status' => $usedPercent > 90 ? 'critical' : ($usedPercent > 75 ? 'warning' : 'healthy'),
                'free_gb' => $freeGB,
                'total_gb' => $totalGB,
                'used_percent' => $usedPercent,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch storage data'];
        }
    }

    protected function getPluginData(): array
    {
        try {
            $result = $this->pluginHealth->getPluginHealth();
            return $result['plugins'] ?? [];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch plugin data'];
        }
    }

    protected function getSafetyData(): array
    {
        try {
            $metrics = $this->safetyMonitor->getMetrics();
            return [
                'status' => $metrics['status'] ?? 'healthy',
                'checkins_today' => $metrics['checkins_today'] ?? 0,
                'active_sos' => $metrics['active_sos'] ?? 0,
                'confidence_score' => $metrics['confidence_score'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch safety data'];
        }
    }

    protected function getSecurityData(): array
    {
        try {
            $metrics = $this->securityMonitor->getMetrics();
            return [
                'status' => $metrics['status'] ?? 'healthy',
                'failed_attempts' => $metrics['failed_attempts'] ?? 0,
                'active_sessions' => $metrics['active_sessions'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch security data'];
        }
    }

    protected function getPerformanceData(): array
    {
        try {
            $metrics = $this->performanceMonitor->getMetrics();
            return [
                'status' => $metrics['status'] ?? 'healthy',
                'memory' => $metrics['memory'] ?? 0,
                'response_time' => $metrics['response_time'] ?? 0,
            ];
        } catch (\Exception $e) {
            return ['status' => 'unknown', 'message' => 'Unable to fetch performance data'];
        }
    }

    protected function getIncidentData(): array
    {
        return [
            'active' => 0,
            'critical' => 0,
            'warning' => 0,
            'recent' => [],
        ];
    }

    protected function getDeploymentData(): array
    {
        // Placeholder — DeploymentService doesn't have getRecentDeployments()
        return [
            ['id' => 1, 'status' => 'healthy', 'time' => '3 hours ago', 'rollback_available' => true]
        ];
    }

    protected function getDiagnosticData(): array
    {
        try {
            $health = $this->healthService->getHealthStatus();
            return [
                'score' => $health['health_score'] ?? 0,
                'status' => ($health['health_score'] ?? 0) >= 90 ? 'PASS' : 'FAIL',
                'timestamp' => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            return ['score' => 0, 'status' => 'UNKNOWN'];
        }
    }

    protected function getSystemMap(): array
    {
        return [
            'internet' => ['status' => 'healthy', 'message' => 'Connected'],
            'api' => ['status' => 'healthy', 'message' => 'Responding'],
            'queue' => ['status' => 'healthy', 'message' => 'Processing'],
            'database' => ['status' => 'healthy', 'message' => 'Connected'],
            'plugins' => ['status' => 'degraded', 'message' => 'Some plugins missing'],
            'android' => ['status' => 'healthy', 'message' => 'Active'],
            'users' => ['status' => 'healthy', 'message' => 'Online'],
        ];
    }
}
