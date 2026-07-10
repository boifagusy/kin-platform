<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SosEvent;
use App\Models\TrustedContact;
use App\Models\SafetyIncident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Events\SOSTriggered;
use App\Services\SafetyScoreService;

class SosController extends Controller
{
    /**
     * Trigger SOS event with rate limiting and proper error messages
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'You must be logged in to trigger SOS.',
                    'code' => 'UNAUTHORIZED',
                ], 401);
            }

            // Rate limit: 3 SOS per hour
            $recentSOS = SafetyIncident::where('user_id', $user->id)
                ->where('type', 'sos')
                ->where('created_at', '>', now()->subHour())
                ->count();

            if ($recentSOS >= 3) {
                return response()->json([
                    'success' => false,
                    'error' => 'Too many SOS requests. Please wait before trying again.',
                    'code' => 'RATE_LIMITED',
                    'retry_after' => $this->getRetryTime($user),
                ], 429);
            }

            // Block if an SOS is already active (unresolved)
            $activeSOS = SosEvent::where('user_id', $user->id)
                ->whereNull('resolved_at')
                ->latest('triggered_at')
                ->first();

            if ($activeSOS) {
                return response()->json([
                    'success' => false,
                    'error' => 'SOS already active. Resolve your current SOS before triggering a new one.',
                    'code' => 'SOS_ALREADY_ACTIVE',
                    'data' => ['sos_id' => $activeSOS->id, 'triggered_at' => $activeSOS->triggered_at],
                ], 409);
            }

            // Cooldown: 5 minutes between SOS triggers, even after resolution (DISABLED FOR TESTING)
            // $lastSOS = SosEvent::where('user_id', $user->id)
            //     ->latest('triggered_at')
            //     ->first();

            // if ($lastSOS) {
            //     $secondsSinceLast = now()->diffInSeconds($lastSOS->triggered_at);
            //     $cooldownSeconds = 300; // 5 minutes
            //     if ($secondsSinceLast < $cooldownSeconds) {
            //         return response()->json([
            //             'success' => false,
            //             'error' => 'Please wait before triggering another SOS.',
            //             'code' => 'SOS_COOLDOWN',
            //             'retry_after' => $cooldownSeconds - $secondsSinceLast,
            //         ], 429);
            //     }
            // }

            return $this->processSOS($request);

        } catch (\Exception $e) {
            Log::error('SOS store error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to trigger SOS: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Process SOS event with validation and trusted contact check
     */
    protected function processSOS(Request $request)
    {
        try {
            $user = $request->user();

            // Validate request
            $validated = $request->validate([
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric',
                'battery_level' => 'nullable|integer|min:0|max:100',
                'is_duress' => 'nullable|boolean',
            ]);

            // Verify active safety network exists
            $activeContacts = TrustedContact::where('user_id', $user->id)
                ->where('active', true)
                ->where('verified', true)
                ->count();

            if ($activeContacts === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'SOS is locked. You must have at least one active verified trusted contact.',
                    'code' => 'NO_TRUSTED_CONTACT',
                ], 422);
            }

            // Check if any contacts are verified
            $verifiedContacts = TrustedContact::where('user_id', $user->id)
                ->where('verified', true)
                ->count();

            if ($verifiedContacts === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'SOS is locked. Your trusted contacts must verify their connection first.',
                    'code' => 'NO_VERIFIED_CONTACT',
                ], 422);
            }

            // Create SOS event
            $sos = SosEvent::create([
                'user_id' => $user->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'battery_level' => $request->battery_level,
                'triggered_at' => now(),
                'resolved_at' => null,
                'is_duress' => $request->is_duress ?? false,
            ]);

            // Create SafetyIncident for consistency
            $incident = SafetyIncident::create([
                'user_id' => $user->id,
                'type' => 'sos',
                'status' => 'active',
                'is_duress' => $request->is_duress ?? false,
                'description' => $request->is_duress ? 'Duress SOS triggered' : 'SOS triggered by user',
                'location' => json_encode([
                    'lat' => $request->latitude,
                    'lng' => $request->longitude,
                ]),
                'confidence_score' => app(SafetyScoreService::class)->getForUser($user),
            ]);

            // Fire event
            event(new SOSTriggered($user, $sos));

            // Log for monitoring
            Log::info('SOS triggered for user: ' . $user->id, [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'battery_level' => $request->battery_level,
                'sos_id' => $sos->id,
                'incident_id' => $incident->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SOS triggered successfully! Your trusted contacts are being notified.',
                'data' => [
                    'sos_id' => $sos->id,
                    'incident_id' => $incident->id,
                    'triggered_at' => $sos->triggered_at,
                    'contacts_notified' => $activeContacts,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed: ' . implode(', ', array_merge(...array_values($e->errors()))),
                'code' => 'VALIDATION_ERROR',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('SOS process error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to process SOS: ' . $e->getMessage(),
                'code' => 'PROCESS_ERROR',
            ], 500);
        }
    }

    /**
     * Resolve SOS event
     */
    public function resolve(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 401);
            }

            $sos = SosEvent::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $sos->update([
                'resolved_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SOS resolved successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to resolve SOS: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get retry time for rate limiting
     */
    protected function getRetryTime($user): int
    {
        $lastSOS = SafetyIncident::where('user_id', $user->id)
            ->where('type', 'sos')
            ->latest()
            ->first();

        if (!$lastSOS) {
            return 0;
        }

        $elapsed = now()->diffInSeconds($lastSOS->created_at);
        return max(0, 3600 - $elapsed);
    }
}
