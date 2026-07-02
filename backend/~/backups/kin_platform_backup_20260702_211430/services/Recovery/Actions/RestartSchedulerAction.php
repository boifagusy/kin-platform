<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Log;

class RestartSchedulerAction extends BaseAction
{
    protected string $name = 'restart_scheduler';
    protected string $description = 'Restart the scheduler';
    
    public function execute(): RecoveryResult
    {
        try {
            shell_exec('pkill -f "schedule:run" 2>/dev/null');
            
            Log::info('Scheduler restart initiated');
            
            return $this->success('Scheduler restarted successfully', [
                'restart_time' => now()->toIso8601String(),
                'method' => 'process_kill_and_cron_restart'
            ]);
        } catch (\Exception $e) {
            Log::error('Restart scheduler failed: ' . $e->getMessage());
            return $this->failed('Failed to restart scheduler: ' . $e->getMessage());
        }
    }
}
