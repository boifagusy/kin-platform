<?php

namespace App\Services\Recovery;

use App\Models\RecoveryPolicy;
use Illuminate\Support\Facades\Log;

class RecoveryPolicyService
{
    public function evaluatePolicies(array $context): array
    {
        $triggered = [];
        $policies = RecoveryPolicy::where('is_active', true)->get();
        
        foreach ($policies as $policy) {
            if ($this->evaluateCondition($policy->trigger_condition, $context)) {
                $triggered[] = $policy;
                Log::info('Policy triggered', ['policy' => $policy->name, 'context' => $context]);
            }
        }
        
        return $triggered;
    }
    
    protected function evaluateCondition(string $condition, array $context): bool
    {
        // Parse condition like "queue_latency > 60"
        // Supports: >, <, >=, <=, ==, !=
        
        $operators = ['>=', '<=', '==', '!=', '>', '<'];
        $operator = null;
        $parts = [];
        
        foreach ($operators as $op) {
            if (strpos($condition, $op) !== false) {
                $operator = $op;
                $parts = explode($op, $condition);
                break;
            }
        }
        
        if (!$operator || count($parts) !== 2) {
            Log::warning('Invalid condition format', ['condition' => $condition]);
            return false;
        }
        
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $actual = $context[$key] ?? null;
        
        if ($actual === null) {
            return false;
        }
        
        return match($operator) {
            '>' => $actual > (float)$value,
            '<' => $actual < (float)$value,
            '>=' => $actual >= (float)$value,
            '<=' => $actual <= (float)$value,
            '==' => $actual == $value,
            '!=' => $actual != $value,
            default => false
        };
    }
}
