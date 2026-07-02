<?php

namespace App\Services\Pulse\Automation;

use App\Models\User;
use App\Models\SafetyEvent;
use App\Models\SecurityEvent;
use Carbon\Carbon;

class SentinelCorrelationService
{
    public function correlateWithSecurityEvents(User $user, SafetyEvent $safetyEvent): array
    {
        // Find related security events in last 24 hours
        $securityEvents = SecurityEvent::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        $correlations = [];
        
        foreach ($securityEvents as $securityEvent) {
            $correlations[] = [
                'security_event_id' => $securityEvent->id,
                'event_type' => $securityEvent->event_type ?? 'unknown',
                'time' => $securityEvent->created_at->diffForHumans(),
                'metadata' => $securityEvent->metadata ?? []
            ];
        }
        
        // Log correlation
        \Log::info('Safety-Security correlation', [
            'user_id' => $user->id,
            'safety_event_id' => $safetyEvent->id,
            'security_events_found' => count($correlations)
        ]);
        
        return $correlations;
    }
    
    public function createSentinelAlert(User $user, SafetyEvent $safetyEvent, array $correlations): void
    {
        if (count($correlations) > 2) {
            // Multiple security events + safety event = high risk
            \Log::critical('HIGH RISK CORRELATION', [
                'user_id' => $user->id,
                'safety_event' => $safetyEvent->event_type,
                'security_events' => count($correlations),
                'requires_immediate_action' => true
            ]);
        }
    }
}
