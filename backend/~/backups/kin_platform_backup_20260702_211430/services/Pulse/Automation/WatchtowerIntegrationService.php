<?php

namespace App\Services\Pulse\Automation;

use App\Models\User;
use App\Models\SafetyEvent;
use App\Models\WatchtowerIncident;
use Carbon\Carbon;

class WatchtowerIntegrationService
{
    public function createIncident(User $user, SafetyEvent $event, array $detections): WatchtowerIncident
    {
        // Check if incident already exists for this event
        $existing = WatchtowerIncident::where('source', 'pulse')
            ->where('source_id', $event->id)
            ->where('status', '!=', 'resolved')
            ->first();
            
        if ($existing) {
            return $existing;
        }
        
        $severity = $this->determineSeverity($detections);
        $title = $this->generateTitle($user, $event);
        $description = $this->generateDescription($user, $event, $detections);
        
        return WatchtowerIncident::create([
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'status' => 'open',
            'source' => 'pulse',
            'source_id' => $event->id,
            'user_id' => $user->id,
            'metadata' => json_encode([
                'safety_event_id' => $event->id,
                'detections' => $detections,
                'triggered_at' => Carbon::now()->toIso8601String()
            ])
        ]);
    }
    
    protected function determineSeverity(array $detections): string
    {
        $severities = array_column($detections, 'severity');
        
        if (in_array('critical', $severities)) {
            return 'critical';
        } elseif (in_array('high', $severities)) {
            return 'high';
        } elseif (in_array('medium', $severities)) {
            return 'medium';
        }
        
        return 'low';
    }
    
    protected function generateTitle(User $user, SafetyEvent $event): string
    {
        return "Safety Alert: {$user->name} - {$event->event_type}";
    }
    
    protected function generateDescription(User $user, SafetyEvent $event, array $detections): string
    {
        $detectionList = implode(', ', array_column($detections, 'type'));
        return "User {$user->name} triggered safety alert.\n" .
               "Event: {$event->event_type}\n" .
               "Detections: {$detectionList}\n" .
               "Time: " . Carbon::now()->toDateTimeString();
    }
}
