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

    public function index(Request $request)
    {
        $modules = [
            'api'           => $this->presentApi($this->raw(fn() => $this->apiMonitor->getMetrics()), 'API Monitor', 'api'),
            'queue'         => $this->presentQueue($this->raw(fn() => $this->queueMonitor->getMetrics()), 'Queue Monitor', 'mail'),
            'database'      => $this->presentDatabase($this->raw(fn() => $this->databaseMonitor->getMetrics()), 'Database Monitor', 'storage'),
            'plugins'       => $this->presentPlugins($this->raw(fn() => $this->pluginHealth->getPluginHealth()), 'Plugin Health', 'extension'),
            'safety'        => $this->presentSafety($this->raw(fn() => $this->safetyEngine->getMetrics()), 'Safety Engine', 'emergency_home'),
            'performance'   => $this->presentPerformance($this->raw(fn() => $this->performanceMonitor->getMetrics()), 'Performance', 'speed'),
            'errors'        => $this->presentErrors($this->raw(fn() => $this->errorMonitor->getMetrics()), 'Errors', 'error'),
            'notifications' => $this->presentNotifications($this->raw(fn() => $this->notificationMonitor->getMetrics()), 'Notifications', 'notifications'),
            'security'      => $this->presentSecurity($this->raw(fn() => $this->securityMonitor->getMetrics()), 'Security', 'security'),
            'system'        => $this->presentSystem($this->raw(fn() => $this->systemHealth->getHealth()), 'System Health', 'monitoring'),
        ];

        $healths = array_column($modules, 'health');

        return view('admin.watchtower.index', [
            'modules'       => $modules,
            'overallStatus' => $this->worst($healths),
            'lastUpdated'   => now()->toISOString(),
            'healthyCount'  => count(array_filter($healths, fn($h) => $h === 'healthy')),
            'warningCount'  => count(array_filter($healths, fn($h) => $h === 'warning')),
            'criticalCount' => count(array_filter($healths, fn($h) => $h === 'critical')),
            'errorCount'    => count(array_filter($healths, fn($h) => $h === 'error')),
        ]);
    }

    // ── Raw data fetch ─────────────────────────────────────────

    private function raw(callable $callable): array
    {
        try {
            return $callable();
        } catch (\Exception $e) {
            Log::error("Watchtower service failed", ['error' => $e->getMessage()]);
            return ['_error' => $e->getMessage()];
        }
    }

    // ── Module Presenters ──────────────────────────────────────

    private function presentApi(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['response_times','error_rates','status_distribution']),
            'items'  => [
                ['label' => 'Avg Response', 'value' => ($d['response_times']['average'] ?? 0) . 'ms'],
                ['label' => 'Error Rate',   'value' => number_format($d['error_rates']['error_rate'] ?? 0, 1) . '%'],
                ['label' => 'Requests',     'value' => number_format($d['error_rates']['total_requests'] ?? 0)],
                ['label' => 'Slow Endpoints','value' => count($d['slow_endpoints'] ?? [])],
            ],
        ];
    }

    private function presentQueue(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['jobs','workers','failed_jobs','status']),
            'items'  => [
                ['label' => 'Pending',    'value' => number_format($d['jobs']['pending'] ?? 0)],
                ['label' => 'Processing', 'value' => number_format($d['jobs']['processing'] ?? 0)],
                ['label' => 'Failed',     'value' => number_format($d['failed_jobs']['failed_count'] ?? 0)],
                ['label' => 'Workers',    'value' => number_format($d['workers']['workers'] ?? 0)],
            ],
        ];
    }

    private function presentDatabase(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $d['connection']['status'] ?? 'unknown',
            'items'  => [
                ['label' => 'Driver',      'value' => $d['connection']['driver'] ?? 'Unknown'],
                ['label' => 'Tables',      'value' => number_format($d['table_stats']['total_tables'] ?? 0)],
                ['label' => 'Connections', 'value' => $d['connections_count']['active'] ?? 'N/A'],
                ['label' => 'Migrations',  'value' => number_format($d['migrations']['total_migrations'] ?? 0)],
            ],
        ];
    }

    private function presentPlugins(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        $plugins = $d['plugins'] ?? [];
        $healthy = count(array_filter($plugins, fn($p) => ($p['status'] ?? '') === 'healthy'));
        $failed = count($plugins) - $healthy;
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $failed > 0 ? 'critical' : 'healthy',
            'items'  => [
                ['label' => 'Active', 'value' => number_format($healthy)],
                ['label' => 'Failed', 'value' => number_format($failed)],
                ['label' => 'Total',  'value' => number_format(count($plugins))],
            ],
        ];
    }

    private function presentSafety(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['sos','checkins','duress','background_tracking']),
            'items'  => [
                ['label' => 'Active SOS',      'value' => number_format($d['sos']['active_sos'] ?? 0)],
                ['label' => 'Missed Check-Ins','value' => $d['checkins']['status'] ?? 'N/A'],
                ['label' => 'Duress Events',   'value' => number_format($d['duress']['duress_events'] ?? 0)],
                ['label' => 'Tracking',        'value' => ($d['background_tracking']['is_active'] ?? false) ? 'Active' : 'Inactive'],
            ],
        ];
    }

    private function presentPerformance(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['system','memory','storage','api_latency']),
            'items'  => [
                ['label' => 'CPU',         'value' => ($d['system']['cpu_usage'] ?? 0) . '%'],
                ['label' => 'Memory',      'value' => ($d['memory']['used_percentage'] ?? 0) . '%'],
                ['label' => 'Disk',        'value' => ($d['storage']['used_percentage'] ?? 0) . '%'],
                ['label' => 'API Latency', 'value' => ($d['api_latency']['average'] ?? 0) . 'ms'],
            ],
        ];
    }

    private function presentErrors(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['laravel_errors','api_errors','network_errors','offline_failures']),
            'items'  => [
                ['label' => 'Laravel Errors',   'value' => number_format($d['laravel_errors']['error_count'] ?? 0)],
                ['label' => 'API Errors',       'value' => number_format($d['api_errors']['total_errors'] ?? 0)],
                ['label' => 'Network Errors',   'value' => number_format($d['network_errors']['failed_requests'] ?? 0)],
                ['label' => 'Offline Failures', 'value' => number_format($d['offline_failures']['failed_syncs'] ?? 0)],
            ],
        ];
    }

    private function presentNotifications(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['sms','push','email','failures']),
            'items'  => [
                ['label' => 'SMS Today',    'value' => number_format($d['sms']['sent_today'] ?? 0)],
                ['label' => 'Push Today',   'value' => number_format($d['push']['sent_today'] ?? 0)],
                ['label' => 'Email Today',  'value' => number_format($d['email']['sent_today'] ?? 0)],
                ['label' => 'Delivery Rate','value' => number_format($d['delivery_rate']['rate'] ?? 0, 1) . '%'],
            ],
        ];
    }

    private function presentSecurity(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $this->worstSub($d, ['failed_logins','jwt_failures','permission_denials','rate_limits']),
            'items'  => [
                ['label' => 'Failed Logins',     'value' => number_format($d['failed_logins']['failed_today'] ?? 0)],
                ['label' => 'JWT Failures',      'value' => number_format($d['jwt_failures']['total_failures'] ?? 0)],
                ['label' => 'Permission Denied', 'value' => number_format($d['permission_denials']['denied_today'] ?? 0)],
                ['label' => 'Rate Limited',      'value' => number_format($d['rate_limits']['rate_limited_today'] ?? 0)],
            ],
        ];
    }

    private function presentSystem(array $d, string $label, string $icon): array
    {
        if (isset($d['_error'])) return $this->errorCard($label, $icon);
        return [
            'label'  => $label, 'icon' => $icon,
            'health' => $d['status'] ?? 'unknown',
            'items'  => [
                ['label' => 'Uptime',  'value' => $d['uptime'] ?? 'N/A'],
                ['label' => 'Version', 'value' => $d['version'] ?? 'Unknown'],
                ['label' => 'Memory',  'value' => $this->fmtBytes($d['memory_usage'] ?? 0)],
                ['label' => 'CPU',     'value' => ($d['cpu_usage'] ?? 0) . '%'],
            ],
        ];
    }

    // ── Helpers ────────────────────────────────────────────────

    private function errorCard(string $label, string $icon): array
    {
        return ['label' => $label, 'icon' => $icon, 'health' => 'error', 'items' => [], 'status' => 'error'];
    }

    private function worstSub(array $data, array $keys): string
    {
        $priority = ['critical' => 3, 'warning' => 2, 'healthy' => 1];
        $worst = 'healthy'; $worstScore = 0;
        foreach ($keys as $k) {
            $s = $data[$k]['status'] ?? 'healthy';
            $score = $priority[$s] ?? 0;
            if ($score > $worstScore) { $worst = $s; $worstScore = $score; }
        }
        return $worst;
    }

    private function worst(array $statuses): string
    {
        $priority = ['critical' => 3, 'warning' => 2, 'healthy' => 1, 'error' => 3];
        $worst = 'healthy'; $worstScore = 0;
        foreach ($statuses as $s) {
            $score = $priority[$s] ?? 0;
            if ($score > $worstScore) { $worst = $s; $worstScore = $score; }
        }
        return $worst;
    }

    private function fmtBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 1) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 0) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 0) . ' KB';
        return $bytes . ' B';
    }
}
