<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerIncident;
use App\Models\WatchtowerAlertRule;

class EscalationService
{
    public function escalate(WatchtowerIncident $incident)
    {
        $rules = WatchtowerAlertRule::where('is_active', true)->get();
        $severity = $incident->severity;
        
        $escalationPath = [
            'critical' => [
                ['time' => 5, 'action' => 'email'],
                ['time' => 15, 'action' => 'sms'],
                ['time' => 30, 'action' => 'push'],
                ['time' => 60, 'action' => 'webhook'],
            ],
            'high' => [
                ['time' => 10, 'action' => 'email'],
                ['time' => 30, 'action' => 'push'],
                ['time' => 60, 'action' => 'webhook'],
            ],
            'medium' => [
                ['time' => 30, 'action' => 'email'],
                ['time' => 60, 'action' => 'push'],
            ],
            'low' => [
                ['time' => 60, 'action' => 'email'],
            ],
        ];

        $path = $escalationPath[$severity] ?? $escalationPath['low'];
        
        $metadata = $incident->metadata ?? [];
        $metadata['escalation_path'] = $path;
        $metadata['escalated_at'] = now()->toISOString();
        $incident->update([
            'is_escalated' => true,
            'metadata' => $metadata,
        ]);
        
        return [
            'incident_id' => $incident->id,
            'severity' => $severity,
            'path' => $path,
            'escalated_at' => now()->toISOString(),
        ];
    }
}
