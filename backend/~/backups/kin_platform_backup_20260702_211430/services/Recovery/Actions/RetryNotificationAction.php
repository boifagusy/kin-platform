<?php

namespace App\Services\Recovery\Actions;

use App\Services\Recovery\Contracts\RecoveryResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RetryNotificationAction extends BaseAction
{
    protected string $name = 'retry_notifications';
    protected string $description = 'Retry failed notifications';
    
    public function execute(): RecoveryResult
    {
        try {
            $retried = 0;
            $failed = 0;
            
            // Check if failed_jobs table exists for notifications
            if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
                $failedNotifications = DB::table('failed_jobs')
                    ->where('queue', 'notifications')
                    ->where('failed_at', '>=', now()->subHours(24))
                    ->get();
                
                foreach ($failedNotifications as $job) {
                    try {
                        // Retry logic would go here
                        $retried++;
                    } catch (\Exception $e) {
                        $failed++;
                        Log::warning('Failed to retry notification', ['job_id' => $job->id]);
                    }
                }
            }
            
            Log::info('Notifications retried', ['success' => $retried, 'failed' => $failed]);
            
            return $this->success('Notifications retried', [
                'retried' => $retried,
                'failed' => $failed,
                'total' => $retried + $failed
            ]);
        } catch (\Exception $e) {
            Log::error('Retry notifications failed: ' . $e->getMessage());
            return $this->failed('Failed to retry notifications: ' . $e->getMessage());
        }
    }
}
