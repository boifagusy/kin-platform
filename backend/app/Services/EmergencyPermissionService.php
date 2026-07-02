<?php

namespace App\Services;

use App\Models\User;
use App\Models\TrustedContact;
use App\Models\SosEvent;
use App\Models\ActivityLog;
use App\Models\EmergencyEscalation;
use Illuminate\Support\Carbon;

class EmergencyPermissionService
{
    /**
     * Normalize phone number to consistent format
     * 08012345678 → +2348012345678
     * 2348012345678 → +2348012345678
     * +2348012345678 → +2348012345678
     */
    public function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all non-digit characters
        $digits = preg_replace('/\D/', '', $phone);

        // 10 digits (local Nigerian number)
        if (strlen($digits) === 10) {
            return '+234' . $digits;
        }

        // 13 digits starting with 234
        if (strlen($digits) === 13 && substr($digits, 0, 3) === '234') {
            return '+' . $digits;
        }

        // Already has + prefix or other format
        if (strlen($phone) > 0 && $phone[0] === '+') {
            return $phone;
        }

        // Return as-is (fallback)
        return $phone;
    }

    /**
     * Check if requestor can access target's location
     */
    public function canAccessLocation(string $requestorPhone, string $targetPhone): array
    {
        // Normalize phones
        $normalizedRequestor = $this->normalizePhone($requestorPhone);
        $normalizedTarget = $this->normalizePhone($targetPhone);

        if (!$normalizedRequestor || !$normalizedTarget) {
            return ['success' => false, 'message' => 'Invalid phone numbers', 'code' => 422];
        }

        // Find users
        $requestor = User::where('phone', $normalizedRequestor)->first();
        $target = User::where('phone', $normalizedTarget)->first();

        if (!$requestor || !$target) {
            return ['success' => false, 'message' => 'User not found', 'code' => 404];
        }

        // Check if requestor is trusted contact of target
        $isTrusted = TrustedContact::where('user_id', $target->id)
            ->where('phone', $requestor->phone)
            ->where('active', true)
            ->exists();

        if (!$isTrusted) {
            return ['success' => false, 'message' => 'Not a trusted contact', 'code' => 403];
        }

        // Check for active emergency (24-hour window for historical events)
        $emergencyWindow = Carbon::now()->subHours(24);

        $hasActiveSos = SosEvent::where('user_id', $target->id)
            ->whereNull('resolved_at')
            ->exists();

        $hasActiveEscalation = EmergencyEscalation::where('user_id', $target->id)
            ->where('status', 'active')
            ->exists();

        $hasRecentMissedCheckin = ActivityLog::where('user_id', $target->id)
            ->where('type', 'CHECKIN_MISSED')
            ->where('occurred_at', '>=', $emergencyWindow)
            ->exists();

        $hasRecentDuress = ActivityLog::where('user_id', $target->id)
            ->where('type', 'DURESS_PIN_USED')
            ->where('occurred_at', '>=', $emergencyWindow)
            ->exists();

        $hasEmergency = $hasActiveSos || $hasActiveEscalation || $hasRecentMissedCheckin || $hasRecentDuress;

        if (!$hasEmergency) {
            return ['success' => false, 'message' => 'No active emergency', 'code' => 403];
        }

        // Get location data
        $locationData = $this->getLocationData($target);

        return [
            'success' => true,
            'data' => $locationData,
            'code' => 200
        ];
    }

    /**
     * Get user's current location (SOS → CheckIn → last_location)
     */
    private function getLocationData(User $user): array
    {
        // Priority 1: Active SOS coordinates
        $activeSos = SosEvent::where('user_id', $user->id)
            ->whereNull('resolved_at')
            ->first();

        if ($activeSos && $activeSos->latitude && $activeSos->longitude) {
            return [
                'latitude' => (float) $activeSos->latitude,
                'longitude' => (float) $activeSos->longitude,
                'maps_url' => $this->generateMapsUrl($activeSos->latitude, $activeSos->longitude),
                'source' => 'sos'
            ];
        }

        // Priority 2: Latest check-in coordinates
        $latestCheckin = $user->checkIns()->latest()->first();

        if ($latestCheckin && $latestCheckin->latitude && $latestCheckin->longitude) {
            return [
                'latitude' => (float) $latestCheckin->latitude,
                'longitude' => (float) $latestCheckin->longitude,
                'maps_url' => $this->generateMapsUrl($latestCheckin->latitude, $latestCheckin->longitude),
                'source' => 'checkin'
            ];
        }

        // Priority 3: User's last known location
        if ($user->last_location) {
            $location = json_decode($user->last_location, true);
            if ($location && isset($location['latitude'], $location['longitude'])) {
                return [
                    'latitude' => (float) $location['latitude'],
                    'longitude' => (float) $location['longitude'],
                    'maps_url' => $this->generateMapsUrl($location['latitude'], $location['longitude']),
                    'source' => 'last_location'
                ];
            }
        }

        return [
            'latitude' => null,
            'longitude' => null,
            'maps_url' => null,
            'source' => null
        ];
    }

    /**
     * Generate Google Maps URL from coordinates
     */
    private function generateMapsUrl(?float $lat, ?float $lng): ?string
    {
        if ($lat === null || $lng === null) {
            return null;
        }
        return "https://maps.google.com/?q={$lat},{$lng}";
    }
}
