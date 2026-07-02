<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ClearFailedJobsAction extends BaseAction
{
    protected string $name = 'clear_failed_jobs';
    protected string $description = 'Clear failed jobs from the queue';
    
    public function execute(): RecoveryResult
    {
        try {
            $before = 0;
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $before = DB::table('failed_jobs')->count();
            }
            
            Artisan::call('queue:flush');
            $output = Artisan::output();
            Log::info('Failed jobs cleared');
            
            $after = 0;
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $after = DB::table('failed_jobs')->count();
            }
            
            return $this->success('Failed jobs cleared successfully', [
                'before_count' => $before,
                'after_count' => $after,
                'cleared' => $before - $after,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Log::error('Clear failed jobs failed: ' . $e->getMessage());
            return $this->failed('Failed to clear jobs: ' . $e->getMessage());
        }
    }
}
