<?php

namespace App\Services\Pulse\Automation;

use App\Models\User;
use App\Models\SafetyEvent;
use App\Models\WatchtowerIncident;
use Illuminate\Support\Facades\Log;

class EscalationService
{
    protected array $escalationLevels = [
        'low' => 1,
        'medium' => 2,
        'high' => 3,
        'critical' => 4
    ];
    
    public function escalate(WatchtowerIncident $incident, array $context = []): void
    {
        $level = $this->escalationLevels[$incident->severity] ?? 1;
        
        // Log escalation
        Log::warning('Incident escalated', [
            'incident_id' => $incident->id,
            'severity' => $incident->severity,
            'level' => $level,
            'context' => $context
        ]);
        
        // Update incident with escalation info
        $incident->metadata = json_encode(array_merge(
            json_decode($incident->metadata ?? '{}', true) ?? [],
            [
                'escalated_at' => now()->toIso8601String(),
                'escalation_level' => $level,
                'escalation_context' => $context
            ]
        ));
        $incident->save();
        
        // Notify appropriate parties based on level
        $this->notifyEscalation($incident, $level);
    }
    
    protected function notifyEscalation(WatchtowerIncident $incident, int $level): void
    {
        if ($level >= 3) {
            // High/critical escalation - notify admin
            Log::critical('CRITICAL ESCALATION', [
                'incident_id' => $incident->id,
                'severity' => $incident->severity,
                'requires_immediate_action' => true
            ]);
        } elseif ($level >= 2) {
            // Medium escalation - notify team lead
            Log::alert('Medium escalation required', [
                'incident_id' => $incident->id,
                'severity' => $incident->severity
            ]);
        }
    }
}
