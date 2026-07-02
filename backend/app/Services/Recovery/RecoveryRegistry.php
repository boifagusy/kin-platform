<?php

namespace App\Services\Recovery;

use App\Services\Recovery\Contracts\RecoveryAction;
use Illuminate\Support\Collection;

class RecoveryRegistry
{
    protected array $actions = [];
    
    public function __construct()
    {
        $this->registerDefaultActions();
    }
    
    protected function registerDefaultActions(): void
    {
        $actions = [
            new \App\Services\Recovery\Actions\RestartQueueWorkerAction(),
            new \App\Services\Recovery\Actions\ClearFailedJobsAction(),
            new \App\Services\Recovery\Actions\ClearStuckQueueAction(),
            new \App\Services\Recovery\Actions\RetryNotificationAction(),
            new \App\Services\Recovery\Actions\RetryWebhookAction(),
            new \App\Services\Recovery\Actions\RotateLogsAction(),
            new \App\Services\Recovery\Actions\CleanTempStorageAction(),
            new \App\Services\Recovery\Actions\OptimizeLaravelAction(),
            new \App\Services\Recovery\Actions\ClearCacheAction(),
            new \App\Services\Recovery\Actions\RestartSchedulerAction(),
        ];
        
        foreach ($actions as $action) {
            $this->register($action);
        }
    }
    
    public function register(RecoveryAction $action): void
    {
        $this->actions[$action->getName()] = $action;
    }
    
    public function getAction(string $name): ?RecoveryAction
    {
        return $this->actions[$name] ?? null;
    }
    
    public function getActions(): Collection
    {
        return collect($this->actions);
    }
    
    public function getSafeActions(): Collection
    {
        return $this->getActions()->filter(fn($action) => $action->isSafe());
    }
}
