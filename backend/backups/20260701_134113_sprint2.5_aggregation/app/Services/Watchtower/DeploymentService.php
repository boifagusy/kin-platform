<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\Cache;

class DeploymentService
{
    public function getDeploymentInfo(): array
    {
        $version = $this->getCurrentVersion();
        $lastDeployment = $this->getLastDeployment();

        return [
            'version' => $version,
            'last_deployment' => $lastDeployment,
            'environment' => app()->environment(),
            'git_commit' => $this->getGitCommit(),
            'rollback_available' => $this->isRollbackAvailable(),
            'uptime' => $this->getUptime(),
        ];
    }

    private function getCurrentVersion(): string
    {
        return config('app.version', '1.0.0');
    }

    private function getLastDeployment(): ?array
    {
        return Cache::get('deployment_last', [
            'timestamp' => now()->toISOString(),
            'duration' => 0,
            'status' => 'unknown',
        ]);
    }

    private function getGitCommit(): string
    {
        if (function_exists('shell_exec')) {
            $commit = shell_exec('git rev-parse --short HEAD 2>/dev/null');
            if ($commit) return trim($commit);
        }
        return 'unknown';
    }

    private function isRollbackAvailable(): bool
    {
        return Cache::get('deployment_rollback_available', false);
    }

    private function getUptime(): string
    {
        if (function_exists('shell_exec')) {
            $uptime = shell_exec('uptime -s 2>/dev/null');
            if ($uptime) return trim($uptime);
        }
        return 'unknown';
    }
}
