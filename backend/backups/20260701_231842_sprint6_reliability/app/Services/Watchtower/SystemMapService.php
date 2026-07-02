<?php

namespace App\Services\Watchtower;

use App\Services\Watchtower\HealthService;

class SystemMapService
{
    private $nodes = [];

    public function __construct(private HealthService $healthService) {}

    public function getSystemMap(): array
    {
        $this->buildNodes();
        return [
            'nodes' => $this->nodes,
            'status' => $this->getOverallStatus(),
            'timestamp' => now()->toISOString(),
        ];
    }

    private function buildNodes(): void
    {
        $health = $this->healthService->getHealthStatus();

        $this->nodes = [
            'react' => [
                'name' => 'React UI',
                'type' => 'frontend',
                'status' => $this->getStatus('healthy'),
                'children' => [],
            ],
            'laravel' => [
                'name' => 'Laravel API',
                'type' => 'backend',
                'status' => $this->getStatus($health['api']['status'] ?? 'unknown'),
                'children' => [],
            ],
            'queue' => [
                'name' => 'Queue System',
                'type' => 'backend',
                'status' => $this->getStatus($health['queue']['status'] ?? 'unknown'),
                'children' => [],
            ],
            'database' => [
                'name' => 'Database',
                'type' => 'data',
                'status' => $this->getStatus($health['database']['status'] ?? 'unknown'),
                'children' => [],
            ],
            'notifications' => [
                'name' => 'Notifications',
                'type' => 'service',
                'status' => $this->getStatus('healthy'),
                'children' => [],
            ],
            'android' => [
                'name' => 'Android Plugins',
                'type' => 'mobile',
                'status' => $this->getStatus('healthy'),
                'children' => [],
            ],
            'users' => [
                'name' => 'Users',
                'type' => 'data',
                'status' => $this->getStatus('healthy'),
                'children' => [],
            ],
        ];
    }

    private function getStatus(string $status): string
    {
        $map = [
            'healthy' => '🟢',
            'ok' => '🟢',
            'warning' => '🟡',
            'degraded' => '🟡',
            'unhealthy' => '🔴',
            'critical' => '🔴',
            'unknown' => '⚪',
        ];
        return $map[$status] ?? '⚪';
    }

    private function getOverallStatus(): string
    {
        $statuses = array_column($this->nodes, 'status');
        if (in_array('🔴', $statuses)) return 'critical';
        if (in_array('🟡', $statuses)) return 'warning';
        return 'healthy';
    }
}
