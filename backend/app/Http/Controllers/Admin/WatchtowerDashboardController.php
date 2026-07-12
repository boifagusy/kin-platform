<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Watchtower\ApiMonitorService;
use App\Services\Watchtower\QueueMonitorService;
use App\Services\Watchtower\DatabaseMonitorService;
use App\Services\Watchtower\PluginHealthService;
use App\Services\Watchtower\SafetyEngineMonitorService;
use App\Services\Watchtower\PerformanceMonitorService;
use App\Services\Watchtower\ErrorMonitorService;
use App\Services\Watchtower\NotificationMonitorService;
use App\Services\Watchtower\SecurityMonitorService;
use App\Services\Watchtower\WatchtowerHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Watchtower Dashboard Controller
 * 
 * Aggregates monitoring data from verified Watchtower services.
 * Passes service-provided overall_health directly to the view.
 * Does NOT calculate thresholds or reinterpret service status.
 * 
 * Brick: watchtower_dashboard
 * Contract: storage/app/contracts/watchtower_services/latest.json
 */
class WatchtowerDashboardController extends Controller
{
    public function __construct(
        private ApiMonitorService $apiMonitor,
        private QueueMonitorService $queueMonitor,
        private DatabaseMonitorService $databaseMonitor,
        private PluginHealthService $pluginHealth,
        private SafetyEngineMonitorService $safetyEngine,
        private PerformanceMonitorService $performanceMonitor,
        private ErrorMonitorService $errorMonitor,
        private NotificationMonitorService $notificationMonitor,
        private SecurityMonitorService $securityMonitor,
        private WatchtowerHealthService $systemHealth,
    ) {}

    /**
     * Display the Watchtower monitoring dashboard.
     * 
     * Each service is called independently. If a service fails,
     * its module shows an error state while others render normally.
     */
    public function index(Request $request)
    {
        $modules = $this->gatherModuleData();
        
        $overallStatus = $this->calculateOverallStatus($modules);

        return view('admin.watchtower.index', [
            'modules' => $modules,
            'overallStatus' => $overallStatus,
            'lastUpdated' => now()->toISOString(),
            'healthyCount' => $this->countByStatus($modules, 'healthy'),
            'warningCount' => $this->countByStatus($modules, 'warning'),
            'criticalCount' => $this->countByStatus($modules, 'critical'),
            'errorCount' => $this->countByStatus($modules, 'error'),
        ]);
    }

    /**
     * Gather data from all monitored services.
     * Each service is wrapped in try/catch for fault isolation.
     */
    private function gatherModuleData(): array
    {
        return [
            'api' => $this->safeCall(
                fn() => $this->apiMonitor->getMetrics(),
                'API Monitor',
                'api'
            ),
            'queue' => $this->safeCall(
                fn() => $this->queueMonitor->getMetrics(),
                'Queue Monitor',
                'mail'
            ),
            'database' => $this->safeCall(
                fn() => $this->databaseMonitor->getMetrics(),
                'Database Monitor',
                'storage'
            ),
            'plugins' => $this->safeCall(
                fn() => $this->pluginHealth->getPluginHealth(),
                'Plugin Health',
                'extension'
            ),
            'safety' => $this->safeCall(
                fn() => $this->safetyEngine->getMetrics(),
                'Safety Engine',
                'emergency_home'
            ),
            'performance' => $this->safeCall(
                fn() => $this->performanceMonitor->getMetrics(),
                'Performance',
                'speed'
            ),
            'errors' => $this->safeCall(
                fn() => $this->errorMonitor->getMetrics(),
                'Errors',
                'error'
            ),
            'notifications' => $this->safeCall(
                fn() => $this->notificationMonitor->getMetrics(),
                'Notifications',
                'notifications'
            ),
            'security' => $this->safeCall(
                fn() => $this->securityMonitor->getMetrics(),
                'Security',
                'security'
            ),
            'system' => $this->safeCall(
                fn() => $this->systemHealth->getHealth(),
                'System Health',
                'monitoring'
            ),
        ];
    }

    /**
     * Execute a service call safely, catching any exceptions.
     * Returns either the service data or an error state.
     */
    private function safeCall(callable $callable, string $label, string $icon): array
    {
        try {
            $data = $callable();
            
            return [
                'status' => 'ok',
                'label' => $label,
                'icon' => $icon,
                'health' => $data['overall_health']['status'] ?? 'unknown',
                'metrics' => $this->extractMetrics($data),
            ];
        } catch (\Exception $e) {
            Log::error("Watchtower dashboard: {$label} unavailable", [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'label' => $label,
                'icon' => $icon,
                'health' => 'error',
                'metrics' => [],
                'error' => 'Unavailable',
            ];
        }
    }

    /**
     * Extract displayable metrics from service data.
     * Excludes metadata keys (overall_health, timestamp).
     */
    private function extractMetrics(array $data): array
    {
        $exclude = ['overall_health', 'timestamp'];
        
        $metrics = [];
        foreach ($data as $key => $value) {
            if (!in_array($key, $exclude)) {
                $metrics[$key] = $value;
            }
        }
        
        return $metrics;
    }

    /**
     * Derive overall platform status from individual module health.
     */
    private function calculateOverallStatus(array $modules): string
    {
        $statuses = array_column($modules, 'health');
        
        if (in_array('critical', $statuses)) {
            return 'critical';
        }
        
        if (in_array('warning', $statuses)) {
            return 'warning';
        }
        
        if (in_array('error', $statuses)) {
            return 'warning';
        }
        
        return 'healthy';
    }

    /**
     * Count modules by health status.
     */
    private function countByStatus(array $modules, string $status): int
    {
        return count(array_filter($modules, fn($m) => ($m['health'] ?? 'unknown') === $status));
    }
}
