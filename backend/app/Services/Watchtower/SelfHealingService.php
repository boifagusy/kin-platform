<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerIncident;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SelfHealingService
{
    protected $allowedActions = [
        'queue' => ['restart'],
        'cache' => ['clear'],
        'logs' => ['rotate'],
    ];

    public function heal(WatchtowerIncident $incident): array
    {
        $result = [
            'incident_id' => $incident->id,
            'actions_taken' => [],
            'success' => false,
            'message' => '',
        ];

        $source = $incident->source;
        $action = $this->determineAction($incident);

        if (!$action) {
            $result['message'] = 'No self-healing action available for this incident.';
            return $result;
        }

        // Check if action is allowed
        if (!$this->isActionAllowed($source, $action)) {
            $result['message'] = "Action '{$action}' on '{$source}' is not allowed.";
            return $result;
        }

        // Execute action
        $success = $this->executeAction($source, $action);

        $result['actions_taken'][] = [
            'source' => $source,
            'action' => $action,
            'success' => $success,
            'timestamp' => now()->toISOString(),
        ];

        $result['success'] = $success;

        if ($success) {
            $result['message'] = "Self-healing action '{$action}' executed successfully.";
            
            // Update incident
            $metadata = $incident->metadata ?? [];
            $metadata['self_healed'] = true;
            $metadata['self_healed_at'] = now()->toISOString();
            $metadata['self_heal_action'] = "{$source}.{$action}";
            $incident->update(['metadata' => $metadata]);
        } else {
            $result['message'] = "Self-healing action '{$action}' failed.";
        }

        return $result;
    }

    protected function determineAction(WatchtowerIncident $incident): ?string
    {
        $actions = [
            'queue' => ['restart', 'clean'],
            'cache' => ['clear'],
            'logs' => ['rotate'],
            'storage' => ['cleanup'],
        ];

        foreach ($actions as $source => $available) {
            if ($source === $incident->source) {
                return $available[0];
            }
        }

        return null;
    }

    protected function isActionAllowed(string $source, string $action): bool
    {
        return isset($this->allowedActions[$source]) &&
               in_array($action, $this->allowedActions[$source]);
    }

    protected function executeAction(string $source, string $action): bool
    {
        try {
            Log::info("Executing self-healing action: {$source}.{$action}");

            $commands = [
                'queue.restart' => 'php artisan queue:restart',
                'cache.clear' => 'php artisan cache:clear',
                'logs.rotate' => 'php artisan log:rotate',
                'storage.cleanup' => 'php artisan kin:cleanup',
            ];

            $command = $commands["{$source}.{$action}"] ?? null;

            if (!$command) {
                return false;
            }

            $result = Process::run($command);
            return $result->successful();
        } catch (\Exception $e) {
            Log::error("Self-healing action failed: {$e->getMessage()}");
            return false;
        }
    }
}
