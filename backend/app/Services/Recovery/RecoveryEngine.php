<?php

namespace App\Services\Recovery;

use App\Models\RecoveryPolicy;
use App\Models\RecoveryAttempt;

class RecoveryEngine
{
    protected RecoveryRegistry $registry;
    protected RecoveryCoordinator $coordinator;
    
    public function __construct(RecoveryRegistry $registry, RecoveryCoordinator $coordinator)
    {
        $this->registry = $registry;
        $this->coordinator = $coordinator;
    }
    
    public function registerAction(Contracts\RecoveryAction $action): void
    {
        $this->registry->register($action);
    }
    
    public function runPolicy(string $policyName, ?string $incidentId = null, ?string $subsystem = null, ?string $trigger = null): RecoveryAttempt
    {
        $policy = RecoveryPolicy::where('name', $policyName)->where('is_active', true)->first();
        if (!$policy) {
            throw new \Exception("Policy not found or inactive: {$policyName}");
        }
        return $this->coordinator->executePolicy($policy, $incidentId, $subsystem, $trigger);
    }
    
    public function runPolicyById(int $policyId, ?string $incidentId = null, ?string $subsystem = null, ?string $trigger = null): RecoveryAttempt
    {
        $policy = RecoveryPolicy::find($policyId);
        if (!$policy || !$policy->is_active) {
            throw new \Exception("Policy not found or inactive: {$policyId}");
        }
        return $this->coordinator->executePolicy($policy, $incidentId, $subsystem, $trigger);
    }
    
    public function getStats(): array
    {
        $total = RecoveryAttempt::count();
        $success = RecoveryAttempt::where('status', 'success')->count();
        $failed = RecoveryAttempt::where('status', 'failed')->count();
        $escalated = RecoveryAttempt::where('escalated', true)->count();
        
        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'escalated' => $escalated,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0
        ];
    }
}
