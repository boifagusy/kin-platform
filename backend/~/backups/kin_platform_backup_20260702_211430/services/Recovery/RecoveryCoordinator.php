<?php

namespace App\Services\Recovery;

use App\Models\RecoveryAttempt;
use App\Models\RecoveryHistory;
use App\Models\RecoveryPolicy;
use App\Services\Recovery\Contracts\RecoveryAction;
use App\Services\Recovery\Contracts\RecoveryResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RecoveryCoordinator
{
    protected RecoveryRegistry $registry;
    protected RecoveryHistoryService $historyService;
    
    public function __construct(RecoveryRegistry $registry, RecoveryHistoryService $historyService)
    {
        $this->registry = $registry;
        $this->historyService = $historyService;
    }
    
    public function executePolicy(RecoveryPolicy $policy, ?string $incidentId = null, ?string $subsystem = null, ?string $trigger = null): RecoveryAttempt
    {
        // Create attempt without action_id (will be set per action execution)
        $attempt = RecoveryAttempt::create([
            'incident_id' => $incidentId,
            'subsystem' => $subsystem,
            'trigger' => $trigger,
            'status' => 'running',
            'started_at' => Carbon::now()
        ]);
        
        $this->historyService->log($attempt, 'started', "Starting recovery policy: {$policy->name}");
        
        $actions = $policy->actions;
        $attemptCount = 0;
        $maxAttempts = $policy->max_attempts;
        $success = false;
        $allResults = [];
        
        while ($attemptCount < $maxAttempts && !$success) {
            $attemptCount++;
            $this->historyService->log($attempt, 'action_executed', "Attempt {$attemptCount} of {$maxAttempts}");
            
            foreach ($actions as $actionName) {
                $action = $this->registry->getAction($actionName);
                if (!$action) {
                    $this->historyService->log($attempt, 'error', "Action not found: {$actionName}");
                    $attempt->update(['status' => 'failed', 'message' => "Action not found: {$actionName}"]);
                    return $attempt;
                }
                
                // Update attempt with current action
                $attempt->recovery_action_id = $this->getActionId($actionName);
                $attempt->save();
                
                $result = $this->executeAction($action, $attempt);
                $allResults[$actionName] = [
                    'status' => $result->getStatus(),
                    'message' => $result->getMessage(),
                    'data' => $result->getData()
                ];
                
                if (!$result->isSuccess()) {
                    $this->historyService->log($attempt, 'action_failed', "Action {$action->getName()} failed: " . $result->getMessage());
                    break 2;
                }
                
                $this->historyService->log($attempt, 'action_success', "Action {$action->getName()} executed successfully");
            }
            
            $success = true;
            $this->historyService->log($attempt, 'completed', "Recovery completed successfully");
        }
        
        $attempt->update([
            'status' => $success ? 'success' : 'failed',
            'finished_at' => Carbon::now(),
            'duration_ms' => $attempt->started_at->diffInMilliseconds(Carbon::now()),
            'data' => $allResults
        ]);
        
        if (!$success && $policy->escalate_on_failure) {
            $attempt->update(['escalated' => true, 'escalation_reason' => 'Policy failed after ' . $maxAttempts . ' attempts']);
            $this->historyService->log($attempt, 'escalated', "Escalated due to failure");
            Log::alert('Recovery escalated', [
                'attempt_id' => $attempt->id,
                'policy' => $policy->name,
                'incident' => $incidentId
            ]);
        }
        
        return $attempt;
    }
    
    protected function getActionId(string $actionName): ?int
    {
        $action = \App\Models\RecoveryAction::where('name', $actionName)->first();
        return $action?->id;
    }
    
    protected function executeAction(RecoveryAction $action, RecoveryAttempt $attempt): RecoveryResult
    {
        $this->historyService->log($attempt, 'action_start', "Executing action: {$action->getName()}");
        try {
            $result = $action->execute();
            $this->historyService->log($attempt, 'action_result', $result->getMessage(), ['status' => $result->getStatus()]);
            return $result;
        } catch (\Exception $e) {
            $this->historyService->log($attempt, 'action_error', "Exception: " . $e->getMessage());
            return new class($e) implements RecoveryResult {
                protected $exception;
                public function __construct($e) { $this->exception = $e; }
                public function getStatus(): string { return 'failed'; }
                public function getMessage(): string { return $this->exception->getMessage(); }
                public function getData(): array { return ['exception' => get_class($this->exception)]; }
                public function isSuccess(): bool { return false; }
            };
        }
    }
}
