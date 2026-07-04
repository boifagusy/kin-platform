<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClearStuckQueueAction extends BaseAction
{
    protected string $name = 'clear_stuck_queue';
    protected string $description = 'Clear stuck jobs from the queue';
    
    public function execute(): RecoveryResult
    {
        try {
            $before = 0;
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $before = DB::table('jobs')->count();
            }
            
            $stuckThreshold = now()->subMinutes(30);
            $stuckJobs = 0;
            
            // Find stuck jobs (older than 30 minutes)
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $stuckJobs = DB::table('jobs')
                    ->where('available_at', '<', $stuckThreshold->timestamp)
                    ->delete();
            }
            
            Log::info('Stuck jobs cleared', ['count' => $stuckJobs]);
            
            $after = 0;
            if (DB::getSchemaBuilder()->hasTable('jobs')) {
                $after = DB::table('jobs')->count();
            }
            
            return $this->success('Stuck jobs cleared successfully', [
                'before_count' => $before,
                'after_count' => $after,
                'cleared' => $stuckJobs,
                'threshold' => $stuckThreshold->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Clear stuck queue failed: ' . $e->getMessage());
            return $this->failed('Failed to clear stuck queue: ' . $e->getMessage());
        }
    }
}
