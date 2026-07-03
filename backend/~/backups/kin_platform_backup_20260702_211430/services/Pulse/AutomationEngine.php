<?php

namespace App\Services\Pulse;

use App\Models\User;
use App\Models\SafetyEvent;
use App\Services\Pulse\Automation\NotificationService;
use App\Services\Pulse\Automation\WatchtowerIntegrationService;
use App\Services\Pulse\Automation\EscalationService;
use App\Services\Pulse\Automation\SentinelCorrelationService;

class AutomationEngine
{
    protected NotificationService $notificationService;
    protected WatchtowerIntegrationService $watchtowerService;
    protected EscalationService $escalationService;
    protected SentinelCorrelationService $sentinelService;
    
    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->watchtowerService = new WatchtowerIntegrationService();
        $this->escalationService = new EscalationService();
        $this->sentinelService = new SentinelCorrelationService();
    }
    
    public function processDetections(User $user, array $detections): array
    {
        $results = [];
        $criticalDetections = [];
        $highDetections = [];
        
        // Separate detections by severity
        foreach ($detections as $detection) {
            if ($detection['severity'] === 'critical') {
                $criticalDetections[] = $detection;
            } elseif ($detection['severity'] === 'high') {
                $highDetections[] = $detection;
            }
        }
        
        // Create or get event
        $event = $this->getOrCreateEvent($user, $detections[0] ?? []);
        
        // Process critical detections
        if (!empty($criticalDetections)) {
            // Create Watchtower incident
            $incident = $this->watchtowerService->createIncident($user, $event, $criticalDetections);
            $results['incident'] = $incident;
            $results['incident_id'] = $incident->id;
            
            // Escalate
            $this->escalationService->escalate($incident, [
                'detections' => $criticalDetections,
                'user_id' => $user->id
            ]);
            
            // Correlate with Sentinel
            $correlations = $this->sentinelService->correlateWithSecurityEvents($user, $event);
            $this->sentinelService->createSentinelAlert($user, $event, $correlations);
            $results['correlations'] = $correlations;
        }
        
        // Process high detections
        if (!empty($highDetections) && empty($criticalDetections)) {
            // Notify guardian
            $this->notificationService->notifyGuardian($user, $event);
            $results['guardian_notified'] = 1;
        }
        
        return $results;
    }
    
    protected function getOrCreateEvent(User $user, array $detection): SafetyEvent
    {
        $type = $detection['type'] ?? 'safety_alert';
        
        $existing = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', $type)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();
            
        if ($existing) {
            return $existing;
        }
        
        return SafetyEvent::create([
            'user_id' => $user->id,
            'event_type' => $type,
            'metadata' => json_encode([
                'severity' => $detection['severity'] ?? 'medium',
                'impact' => $detection['impact'] ?? 20,
                'description' => $detection['description'] ?? 'Automated detection',
                'automated' => true
            ])
        ]);
    }
}
