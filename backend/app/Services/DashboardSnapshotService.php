<?php

namespace App\Services;

use App\Models\User;
use App\Models\CheckIn;
use App\Models\CheckinSetting;
use App\Models\ActivityLog;
use App\Models\TrustedContact;
use Illuminate\Support\Facades\Cache;

class DashboardSnapshotService
{
    private const CACHE_TTL = 300;

    private SafetyScoreService $scoreService;

    public function __construct(SafetyScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }

    public function getSnapshot(User $user): array
    {
        $cacheKey = "dashboard:{$user->id}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($user) {
            return $this->buildSnapshot($user);
        });
    }

    public function refresh(User $user): void
    {
        Cache::forget("dashboard:{$user->id}");
        $this->getSnapshot($user);
    }

    private function buildSnapshot(User $user): array
    {
        $settings = CheckinSetting::where('user_id', $user->id)->first();
        $lastCheckIn = CheckIn::where('user_id', $user->id)->latest()->first();
        $score = $this->scoreService->getForUser($user);

        // Get recent activities
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->where('occurred_at', '>=', now()->subDays(30))
            ->orderBy('occurred_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($activity) {
                $details = $activity->details;
                $message = $activity->type ?? 'Activity recorded';

                if (is_string($details)) {
                    $decoded = json_decode($details, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $message = $this->formatActivityMessage($activity->type, $decoded);
                    } else {
                        $message = is_string($details) ? $details : json_encode($details);
                        if (strlen($message) > 100) {
                            $message = substr($message, 0, 100) . '...';
                        }
                    }
                } else if (is_array($details)) {
                    $message = $this->formatActivityMessage($activity->type, $details);
                }

                $icon = $this->getIconForType($activity->type, $message);
                $timeAgo = $activity->occurred_at ? $activity->occurred_at->diffForHumans() : '';

                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'icon' => $icon,
                    'message' => $message,
                    'time_ago' => $timeAgo,
                    'timestamp' => $activity->occurred_at ? $activity->occurred_at->toISOString() : null,
                ];
            })
            ->toArray();

        $safeZones = [
            ['id' => 1, 'name' => 'Home', 'address' => 'Your home address', 'active' => true],
            ['id' => 2, 'name' => 'Work', 'address' => 'Your work address', 'active' => false],
        ];

        $trustedContactsCount = TrustedContact::where('user_id', $user->id)
            ->where('verified', true)
            ->count();

        $hasVerifiedContact = TrustedContact::where('user_id', $user->id)
            ->where('verified', true)
            ->where('active', true)
            ->exists();

        $recentCheckIn = CheckIn::where('user_id', $user->id)
            ->where('checked_in_at', '>=', now()->subHours(24))
            ->exists();

        return [
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'contacts_count' => $trustedContactsCount,
            ],
            'trusted_contact' => $user->trustedContacts()->where('verified', true)->first() ? [
                'id' => $user->trustedContacts()->where('verified', true)->first()->id,
                'name' => $user->trustedContacts()->where('verified', true)->first()->name,
                'phone' => $user->trustedContacts()->where('verified', true)->first()->phone,
                'verified' => true,
            ] : null,
            'safety_score' => $score,
            'score_label' => $this->getScoreLabel($score),
            'last_checkin' => $lastCheckIn?->checked_in_at,
            'settings' => [
                'checkin_time' => substr($settings->checkin_time ?? '21:00', 0, 5),
                'grace_minutes' => $settings->grace_minutes ?? 15,
                'enabled' => $settings->enabled ?? true,
            ],
            'activities' => $recentActivities,
            'activities_total' => count($recentActivities),
            'safe_zones' => $safeZones,
            'pending_tasks' => $this->getPendingTasks($user),
            'snapshot_generated_at' => now()->toISOString(),
            'unread_alerts' => \App\Models\SafetyIncident::where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
            // ✅ NEW FIELDS FOR SAFETY STATUS
            'phone_verified' => !empty($user->phone_verified_at),
            'pin_created' => !empty($user->login_pin_hash),
            'has_verified_contact' => $hasVerifiedContact,
            'recent_checkin' => $recentCheckIn,
            // ✅ ADDED: Duress PIN field
            'duress_pin_created' => !empty($user->duress_pin_hash),
        ];
    }

    private function formatActivityMessage($type, $details): string
    {
        if (isset($details['sos_id'])) {
            return "🚨 SOS Alert triggered (ID: {$details['sos_id']})";
        }
        if (isset($details['incident_id'])) {
            return "📋 Safety incident (ID: {$details['incident_id']})";
        }
        if (isset($details['escalation_id'])) {
            return "⬆️ Escalation triggered (ID: {$details['escalation_id']})";
        }
        if (isset($details['contacts_notified'])) {
            return "📱 {$details['contacts_notified']} contact(s) notified";
        }
        if (isset($details['check_in_id'])) {
            return "✅ Check-in completed (ID: {$details['check_in_id']})";
        }
        if ($type === 'SOS_TRIGGERED' || $type === 'sos_triggered') {
            return "🚨 SOS Alert triggered";
        }
        if ($type === 'DURESS_PIN_USED' || $type === 'duress_pin_used') {
            return "⚠️ Duress PIN used - Silent SOS triggered";
        }
        if (strpos($type, 'CHECKIN') !== false || strpos($type, 'checkin') !== false) {
            return "✅ Check-in completed";
        }
        return $type ?? 'Activity recorded';
    }

    private function getIconForType($type, $message = ''): string
    {
        if (strpos($message, '🚨') !== false) return '🚨';
        if (strpos($message, '📋') !== false) return '📋';
        if (strpos($message, '⬆️') !== false) return '⬆️';
        if (strpos($message, '📱') !== false) return '📱';
        if (strpos($message, '✅') !== false) return '✅';
        if (strpos($message, '⚠️') !== false) return '⚠️';

        $typeLower = strtolower($type);
        if (strpos($typeLower, 'sos') !== false) return '🚨';
        if (strpos($typeLower, 'duress') !== false) return '⚠️';
        if (strpos($typeLower, 'checkin') !== false) return '✅';
        if (strpos($typeLower, 'reminder') !== false) return '🔔';
        if (strpos($typeLower, 'alert') !== false) return '🚨';
        return '📋';
    }

    private function getScoreLabel(int $score): string
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 70) return 'Good';
        if ($score >= 50) return 'Fair';
        return 'Needs Attention';
    }

    private function getPendingTasks(User $user): array
    {
        $tasks = [];
        $hasSafeZones = false;
        $hasDuressPin = !empty($user->duress_pin_hash);
        $hasLocationEnabled = false;
        $hasVerifiedContact = TrustedContact::where('user_id', $user->id)
            ->where('verified', true)
            ->exists();

        if (!$hasLocationEnabled) {
            $tasks[] = ['id' => 'location', 'title' => 'Enable Location', 'description' => 'Required for emergency alerts'];
        }
        if (!$hasSafeZones) {
            $tasks[] = ['id' => 'safe_zones', 'title' => 'Add Safe Zones', 'description' => 'Set your safe places'];
        }
        if (!$hasDuressPin) {
            $tasks[] = ['id' => 'duress_pin', 'title' => 'Create Duress PIN', 'description' => 'Silent emergency alert'];
        }
        if (!$hasVerifiedContact) {
            $tasks[] = ['id' => 'trusted_contact', 'title' => 'Add Trusted Contact', 'description' => 'Someone to notify in emergencies'];
        }
        return $tasks;
    }
}
