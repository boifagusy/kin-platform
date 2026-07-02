<?php

namespace App\Traits;

use App\Models\SecurityEvent;
use Illuminate\Support\Facades\Log;

trait LogsSecurityEvents
{
    protected function logSecurityEvent(string $eventType, string $severity, array $details = []): void
    {
        try {
            $event = new SecurityEvent();
            $event->event_type = $eventType;
            $event->severity = $severity;
            $event->source_ip = request()->ip() ?? 'unknown';
            $event->user_agent = request()->userAgent() ?? 'unknown';
            $event->user_id = auth()->id();
            $event->details = $details;
            $event->save();
        } catch (\Exception $e) {
            Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }

    protected function logSecurityEventWithUser(int $userId, string $eventType, string $severity, array $details = []): void
    {
        try {
            $event = new SecurityEvent();
            $event->event_type = $eventType;
            $event->severity = $severity;
            $event->source_ip = request()->ip() ?? 'unknown';
            $event->user_agent = request()->userAgent() ?? 'unknown';
            $event->user_id = $userId;
            $event->details = $details;
            $event->save();
        } catch (\Exception $e) {
            Log::error('Failed to log security event: ' . $e->getMessage());
        }
    }
}
