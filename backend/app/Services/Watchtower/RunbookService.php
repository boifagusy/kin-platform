<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerRunbook;

class RunbookService
{
    public function getRunbook(string $trigger): ?array
    {
        $runbook = WatchtowerRunbook::where('trigger_condition', $trigger)
            ->where('is_active', true)
            ->first();

        if (!$runbook) {
            return null;
        }

        return [
            'title' => $runbook->title,
            'description' => $runbook->description,
            'impact' => $runbook->impact,
            'recommended_actions' => $runbook->recommended_actions,
            'estimated_recovery_time' => $runbook->estimated_recovery_time,
            'commands' => $this->extractCommands($runbook->recommended_actions),
        ];
    }

    public function getRunbookForIncident(WatchtowerIncident $incident): ?array
    {
        $triggers = [
            'storage_critical' => ['storage', 'storage_usage'],
            'api_timeout' => ['api', 'api_latency'],
            'queue_backlog' => ['queue', 'queue_size'],
            'plugin_offline' => ['plugin', 'plugin_health'],
            'database_slow' => ['database', 'database_latency'],
        ];

        $source = $incident->source;
        $trigger = null;

        foreach ($triggers as $key => $sources) {
            if (in_array($source, $sources)) {
                $trigger = $key;
                break;
            }
        }

        return $trigger ? $this->getRunbook($trigger) : null;
    }

    protected function extractCommands(string $text): array
    {
        preg_match_all('/`([^`]+)`/', $text, $matches);
        return $matches[1] ?? [];
    }

    public function createDefaultRunbooks(): void
    {
        $runbooks = [
            [
                'title' => 'Storage Critical',
                'trigger_condition' => 'storage_critical',
                'description' => 'Disk usage exceeds 90% of available space.',
                'impact' => 'Builds may fail. Database writes may be blocked.',
                'recommended_actions' => "1. Check storage usage\n2. Run cleanup: `php artisan kin:cleanup`\n3. Archive old logs\n4. Remove old caches\n5. Monitor space recovery",
                'estimated_recovery_time' => '5-10 minutes',
                'is_active' => true,
            ],
            [
                'title' => 'API Timeout',
                'trigger_condition' => 'api_timeout',
                'description' => 'API response time exceeds threshold.',
                'impact' => 'Users may experience slow responses.',
                'recommended_actions' => "1. Check API logs: `tail -f storage/logs/laravel.log`\n2. Check database connections\n3. Increase worker count\n4. Cache heavy responses",
                'estimated_recovery_time' => '10-15 minutes',
                'is_active' => true,
            ],
            [
                'title' => 'Queue Backlog',
                'trigger_condition' => 'queue_backlog',
                'description' => 'Queue has more than 100 pending jobs.',
                'impact' => 'Jobs are not being processed in a timely manner.',
                'recommended_actions' => "1. Check queue workers: `php artisan queue:list`\n2. Restart workers: `php artisan queue:restart`\n3. Check failed jobs: `php artisan queue:failed`",
                'estimated_recovery_time' => '5-10 minutes',
                'is_active' => true,
            ],
            [
                'title' => 'Plugin Offline',
                'trigger_condition' => 'plugin_offline',
                'description' => 'A Capacitor plugin is not responding.',
                'impact' => 'App features may not work correctly.',
                'recommended_actions' => "1. Check plugin logs\n2. Verify permissions\n3. Reinstall plugin\n4. Test plugin health",
                'estimated_recovery_time' => '15-20 minutes',
                'is_active' => true,
            ],
        ];

        foreach ($runbooks as $runbook) {
            WatchtowerRunbook::updateOrCreate(
                ['trigger_condition' => $runbook['trigger_condition']],
                $runbook
            );
        }
    }
}
