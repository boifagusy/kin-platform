<?php

namespace App\Services\Sentinel;

use App\Models\SecurityEvent;
use Illuminate\Http\Request;

class SecurityService
{
    public function logEvent(string $eventType, string $severity, array $details = []): SecurityEvent
    {
        $event = new SecurityEvent();
        $event->event_type = $eventType;
        $event->severity = $severity;
        $event->source_ip = request()->ip() ?? 'unknown';
        $event->user_agent = request()->userAgent() ?? 'unknown';
        $event->user_id = auth()->id();
        $event->details = $details;
        $event->save();

        return $event;
    }

    public function logLoginAttempt(string $phone, bool $success, string $reason = null): SecurityEvent
    {
        return $this->logEvent(
            $success ? 'login_success' : 'login_failed',
            $success ? 'info' : 'warning',
            [
                'phone' => $phone,
                'success' => $success,
                'reason' => $reason,
            ]
        );
    }

    public function getRecentEvents(int $limit = 50): array
    {
        return SecurityEvent::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getSecurityStats(): array
    {
        $today = now()->startOfDay();

        return [
            'total_events' => SecurityEvent::count(),
            'today_events' => SecurityEvent::where('created_at', '>=', $today)->count(),
            'critical_events' => SecurityEvent::where('severity', 'critical')->count(),
            'failed_logins' => SecurityEvent::where('event_type', 'login_failed')->count(),
            'suspicious_events' => SecurityEvent::where('details->suspicious', true)->count(),
        ];
    }

    public function detectSuspiciousActivity(): array
    {
        $suspicious = [];

        // Check for multiple failed logins
        $failedLogins = SecurityEvent::where('event_type', 'login_failed')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($failedLogins >= 5) {
            $suspicious[] = [
                'type' => 'brute_force_attempt',
                'severity' => 'critical',
                'message' => "{$failedLogins} failed login attempts in 15 minutes",
            ];
        }

        return $suspicious;
    }
}
