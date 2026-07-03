<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RestartQueueWorkerAction extends BaseAction
{
    protected string $name = 'restart_queue_worker';
    protected string $description = 'Restart Laravel queue workers';
    
    public function execute(): RecoveryResult
    {
        try {
            // Get current queue status
            $before = $this->getQueueStatus();
            
            // Restart queue
            Artisan::call('queue:restart');
            Log::info('Queue worker restart initiated');
            
            // Wait for restart to take effect
            sleep(2);
            
            // Verify
            $after = $this->getQueueStatus();
            
            return $this->success('Queue workers restarted successfully', [
                'before' => $before,
                'after' => $after,
                'restart_time' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Queue restart failed: ' . $e->getMessage());
            return $this->failed('Queue restart failed: ' . $e->getMessage());
        }
    }
    
    protected function getQueueStatus(): array
    {
        try {
            $queueCount = 0;
            $failedCount = 0;
            
            // Check if jobs table exists
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $queueCount = DB::table('jobs')->count() ?? 0;
            }
            
            // Check if failed_jobs table exists
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedCount = DB::table('failed_jobs')->count() ?? 0;
            }
            
            return [
                'queue_count' => $queueCount,
                'failed_count' => $failedCount,
                'workers_running' => $this->areWorkersRunning()
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    protected function areWorkersRunning(): bool
    {
        $output = shell_exec('ps aux 2>/dev/null | grep "queue:work" | grep -v grep');
        return !empty($output);
    }
}
