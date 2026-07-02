<?php

namespace App\Services;

use App\Services\Watchtower\HealthService;
use App\Services\Watchtower\SystemMapService;
use App\Services\Watchtower\SelfTestService;
use App\Services\Watchtower\DeploymentService;
use App\Services\Watchtower\HealthScoreService;

class KINGuardianService
{
    public function __construct(
        private HealthService $healthService,
        private SystemMapService $systemMapService,
        private SelfTestService $selfTestService,
        private DeploymentService $deploymentService,
        private HealthScoreService $healthScoreService
    ) {}

    public function getDashboard(): array
    {
        $health = $this->healthService->getHealthStatus();
        $systemMap = $this->systemMapService->getSystemMap();
        $selfTest = $this->selfTestService->runAllTests();
        $deployment = $this->deploymentService->getDeploymentInfo();

        $components = $this->extractComponentStatuses($health);
        $healthScore = $this->healthScoreService->getDetailedHealthScore($components);

        return [
            'health' => $healthScore,
            'system_map' => $systemMap,
            'self_test' => $selfTest,
            'deployment' => $deployment,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function extractComponentStatuses(array $health): array
    {
        $components = [];
        $keys = ['api', 'database', 'cache', 'storage', 'queue', 'scheduler'];

        foreach ($keys as $key) {
            if (isset($health[$key]['status'])) {
                $components[$key] = $health[$key]['status'];
            }
        }

        if (isset($health['disk_usage']['status'])) {
            $components['disk'] = $health['disk_usage']['status'];
        }

        if (isset($health['memory']['status'])) {
            $components['memory'] = $health['memory']['status'];
        }

        return $components;
    }
}
