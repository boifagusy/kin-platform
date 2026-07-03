<?php

namespace App\Services\Guardian;

use App\Services\Recovery\RecoveryEngine;
use App\Models\RecoveryPolicy;
use Illuminate\Support\Facades\Log;

class GuardianRecoveryService
{
    protected RecoveryEngine $recoveryEngine;
    
    public function __construct(RecoveryEngine $recoveryEngine)
    {
        $this->recoveryEngine = $recoveryEngine;
    }
    
    /**
     * Trigger recovery based on Guardian alert
     */
    public function handleAlert(array $alert): array
    {
        $results = [];
        
        // Determine which policy to run based on alert type
        $policyName = $this->determinePolicy($alert);
        
        if (!$policyName) {
            Log::info('No matching policy for alert', ['alert' => $alert]);
            return ['status' => 'no_policy', 'message' => 'No matching policy found'];
        }
        
        // Run the policy
        $attempt = $this->recoveryEngine->runPolicy(
            $policyName,
            $alert['incident_id'] ?? null,
            $alert['subsystem'] ?? null,
            $alert['trigger'] ?? 'guardian_alert'
        );
        
        return [
            'status' => $attempt->status,
            'attempt_id' => $attempt->id,
            'policy' => $policyName,
            'message' => $attempt->message,
            'escalated' => $attempt->escalated
        ];
    }
    
    protected function determinePolicy(array $alert): ?string
    {
        $type = $alert['type'] ?? '';
        $severity = $alert['severity'] ?? '';
        
        // Map alert types to recovery policies
        $mapping = [
            'queue_unhealthy' => 'queue_health_recovery',
            'queue_latency' => 'queue_health_recovery',
            'disk_full' => 'disk_cleanup',
            'disk_usage' => 'disk_cleanup',
            'notification_failure' => 'notification_retry',
            'webhook_failure' => 'notification_retry',
            'cache_corruption' => 'cache_clear',
        ];
        
        // Check for exact match
        if (isset($mapping[$type])) {
            return $mapping[$type];
        }
        
        // Check for partial matches
        foreach ($mapping as $key => $policy) {
            if (strpos($type, $key) !== false) {
                return $policy;
            }
        }
        
        // Check severity-based fallback
        if ($severity === 'critical') {
            return 'queue_health_recovery';
        }
        
        return null;
    }
}
