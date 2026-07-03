<?php

namespace App\Services\Guardian;

use App\Models\WatchtowerIncident;
use App\Models\SecurityEvent;
use App\Models\SafetyEvent;
use App\Models\SafetyScore;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GuardianTimelineService
{
    public function getTimeline(int $limit = 50): array
    {
        $events = collect();

        // Get Watchtower incidents
        $incidents = WatchtowerIncident::with('user')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return $this->formatEvent($item, 'watchtower');
            });
        $events = $events->merge($incidents);

        // Get Security events
        $securityEvents = SecurityEvent::with('user')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return $this->formatEvent($item, 'sentinel');
            });
        $events = $events->merge($securityEvents);

        // Get Safety events
        $safetyEvents = SafetyEvent::with('user')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return $this->formatEvent($item, 'pulse');
            });
        $events = $events->merge($safetyEvents);

        // Get Safety scores
        $scores = SafetyScore::with('user')
            ->orderBy('calculated_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return $this->formatEvent($item, 'pulse', 'score');
            });
        $events = $events->merge($scores);

        // Sort by time
        return $events->sortByDesc('timestamp')->values()->toArray();
    }

    protected function formatEvent($event, string $source, string $type = 'event'): array
    {
        $timestamp = $event->created_at ?? $event->calculated_at ?? Carbon::now();
        $userName = $event->user->name ?? 'System';

        return [
            'id' => $event->id,
            'source' => $source,
            'type' => $type,
            'event_type' => $event->event_type ?? $event->type ?? 'unknown',
            'user' => $userName,
            'severity' => $event->severity ?? 'info',
            'message' => $this->generateMessage($event, $source),
            'timestamp' => $timestamp->toIso8601String(),
            'time_ago' => $timestamp->diffForHumans()
        ];
    }

    protected function generateMessage($event, string $source): string
    {
        $messages = [
            'watchtower' => "Watchtower: " . ($event->title ?? 'Incident'),
            'sentinel' => "Sentinel: Security event - " . ($event->event_type ?? 'unknown'),
            'pulse' => "Pulse: Safety event - " . ($event->event_type ?? 'score'),
        ];

        return $messages[$source] ?? "Event from {$source}";
    }
}
