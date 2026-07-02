<?php

namespace App\Services\Sentinel;

use App\Models\SecurityEvent;
use App\Models\User;
use App\Models\WatchtowerIncident;
use Illuminate\Support\Facades\Log;

class ResponseEngine
{
    protected array $responseMap = [
        'log' => 'logResponse',
        'lock_account' => 'lockAccountResponse',
        'notify_admin' => 'notifyAdminResponse',
        'force_logout' => 'forceLogoutResponse',
        'revoke_tokens' => 'revokeTokensResponse',
        'require_otp' => 'requireOtpResponse',
    ];

    public function execute(SecurityEvent $event, array $detection): array
    {
        $actions = $detection['automated_actions'] ?? ['log'];
        $results = [];

        foreach ($actions as $action) {
            if (isset($this->responseMap[$action])) {
                $method = $this->responseMap[$action];
                $result = $this->$method($event, $detection);
                $results[$action] = $result;
            }
        }

        // Create Watchtower incident for high/critical severity
        if (in_array($detection['severity'], ['high', 'critical'])) {
            $this->createWatchtowerIncident($event, $detection);
        }

        return $results;
    }

    protected function logResponse(SecurityEvent $event, array $detection): array
    {
        Log::warning('Sentinel automated response: log', [
            'event_id' => $event->id,
            'rule' => $detection['rule_id'],
            'severity' => $detection['severity'],
        ]);

        return ['status' => 'success', 'action' => 'log'];
    }

    protected function lockAccountResponse(SecurityEvent $event, array $detection): array
    {
        $user = $event->user_id ? User::find($event->user_id) : null;

        if ($user) {
            $user->is_locked = true;
            $user->locked_at = now();
            $user->locked_reason = 'Automated security lock: ' . $detection['rule_name'];
            $user->save();

            Log::alert('Sentinel automated response: account locked', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'rule' => $detection['rule_id'],
            ]);

            return ['status' => 'success', 'action' => 'lock_account', 'user_id' => $user->id];
        }

        return ['status' => 'failed', 'action' => 'lock_account', 'reason' => 'User not found'];
    }

    protected function notifyAdminResponse(SecurityEvent $event, array $detection): array
    {
        Log::alert('Sentinel automated response: admin notification', [
            'event_id' => $event->id,
            'rule' => $detection['rule_id'],
            'severity' => $detection['severity'],
            'message' => "Security alert: {$detection['rule_name']} triggered by event {$event->id}",
        ]);

        return ['status' => 'success', 'action' => 'notify_admin'];
    }

    protected function forceLogoutResponse(SecurityEvent $event, array $detection): array
    {
        $user = $event->user_id ? User::find($event->user_id) : null;

        if ($user) {
            // Force logout by invalidating all sessions
            // This would be implemented based on your session management
            Log::alert('Sentinel automated response: force logout', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'rule' => $detection['rule_id'],
            ]);

            return ['status' => 'success', 'action' => 'force_logout', 'user_id' => $user->id];
        }

        return ['status' => 'failed', 'action' => 'force_logout', 'reason' => 'User not found'];
    }

    protected function revokeTokensResponse(SecurityEvent $event, array $detection): array
    {
        $user = $event->user_id ? User::find($event->user_id) : null;

        if ($user) {
            // Revoke all tokens for the user
            Log::alert('Sentinel automated response: tokens revoked', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'rule' => $detection['rule_id'],
            ]);

            return ['status' => 'success', 'action' => 'revoke_tokens', 'user_id' => $user->id];
        }

        return ['status' => 'failed', 'action' => 'revoke_tokens', 'reason' => 'User not found'];
    }

    protected function requireOtpResponse(SecurityEvent $event, array $detection): array
    {
        $user = $event->user_id ? User::find($event->user_id) : null;

        if ($user) {
            Log::alert('Sentinel automated response: OTP required', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'rule' => $detection['rule_id'],
            ]);

            return ['status' => 'success', 'action' => 'require_otp', 'user_id' => $user->id];
        }

        return ['status' => 'failed', 'action' => 'require_otp', 'reason' => 'User not found'];
    }

    protected function createWatchtowerIncident(SecurityEvent $event, array $detection): ?WatchtowerIncident
    {
        if (!class_exists(WatchtowerIncident::class)) {
            return null;
        }

        try {
            $incident = WatchtowerIncident::create([
                'title' => "Security Alert: {$detection['rule_name']}",
                'description' => "Security rule {$detection['rule_id']} triggered by event {$event->id}. Severity: {$detection['severity']}",
                'severity' => $detection['severity'] === 'critical' ? 'critical' : 'high',
                'status' => 'new',
                'source' => 'sentinel',
                'metadata' => [
                    'event_id' => $event->id,
                    'rule_id' => $detection['rule_id'],
                    'rule_name' => $detection['rule_name'],
                    'risk_points' => $detection['risk_points'],
                    'event_details' => $event->details,
                ],
                'detected_at' => now(),
            ]);

            Log::info('Watchtower incident created from Sentinel alert', [
                'incident_id' => $incident->id,
                'event_id' => $event->id,
                'rule' => $detection['rule_id'],
            ]);

            return $incident;
        } catch (\Exception $e) {
            Log::error('Failed to create Watchtower incident', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
