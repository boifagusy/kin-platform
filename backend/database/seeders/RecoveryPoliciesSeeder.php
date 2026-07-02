<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RecoveryAction;
use App\Models\RecoveryPolicy;

class RecoveryPoliciesSeeder extends Seeder
{
    public function run(): void
    {
        // Create default actions (these will be implemented in Sprint R2)
        $actions = [
            ['name' => 'restart_queue_worker', 'class' => 'App\Services\Recovery\Actions\RestartQueueWorkerAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'clear_failed_jobs', 'class' => 'App\Services\Recovery\Actions\ClearFailedJobsAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'clear_stuck_queue', 'class' => 'App\Services\Recovery\Actions\ClearStuckQueueAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'retry_notifications', 'class' => 'App\Services\Recovery\Actions\RetryNotificationAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'retry_webhooks', 'class' => 'App\Services\Recovery\Actions\RetryWebhookAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'rotate_logs', 'class' => 'App\Services\Recovery\Actions\RotateLogsAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'clean_temp_storage', 'class' => 'App\Services\Recovery\Actions\CleanTempStorageAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'optimize_laravel', 'class' => 'App\Services\Recovery\Actions\OptimizeLaravelAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'clear_cache', 'class' => 'App\Services\Recovery\Actions\ClearCacheAction', 'is_safe' => true, 'is_rollbackable' => false],
            ['name' => 'restart_scheduler', 'class' => 'App\Services\Recovery\Actions\RestartSchedulerAction', 'is_safe' => true, 'is_rollbackable' => false],
        ];
        
        foreach ($actions as $actionData) {
            RecoveryAction::updateOrCreate(['name' => $actionData['name']], $actionData);
        }
        
        // Create default policies
        $policies = [
            [
                'name' => 'queue_health_recovery',
                'trigger_condition' => 'queue_latency > 60',
                'actions' => ['clear_stuck_queue', 'restart_queue_worker'],
                'max_attempts' => 3,
                'retry_delay_seconds' => 60,
                'escalate_on_failure' => true,
                'escalation_level' => 'high'
            ],
            [
                'name' => 'disk_cleanup',
                'trigger_condition' => 'disk_usage > 95',
                'actions' => ['clean_temp_storage', 'rotate_logs'],
                'max_attempts' => 2,
                'retry_delay_seconds' => 300,
                'escalate_on_failure' => true,
                'escalation_level' => 'medium'
            ],
            [
                'name' => 'notification_retry',
                'trigger_condition' => 'notification_failure_rate > 10',
                'actions' => ['retry_notifications', 'retry_webhooks'],
                'max_attempts' => 3,
                'retry_delay_seconds' => 120,
                'escalate_on_failure' => true,
                'escalation_level' => 'low'
            ],
            [
                'name' => 'cache_clear',
                'trigger_condition' => 'cache_corruption_detected',
                'actions' => ['clear_cache', 'optimize_laravel'],
                'max_attempts' => 2,
                'retry_delay_seconds' => 30,
                'escalate_on_failure' => false,
                'escalation_level' => 'low'
            ],
        ];
        
        foreach ($policies as $policyData) {
            RecoveryPolicy::updateOrCreate(['name' => $policyData['name']], $policyData);
        }
    }
}
