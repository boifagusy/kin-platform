<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Services\CheckInService;
use App\Events\CheckInCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckInController extends Controller
{
    protected $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'You must be logged in to check in.',
                ], 401);
            }

            $validated = $request->validate([
                'status' => 'nullable|string|in:safe,unsafe',
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'accuracy' => 'nullable|numeric',
            ]);

            $status = $validated['status'] ?? 'safe';
            $location = null;
            
            if (isset($validated['latitude']) && isset($validated['longitude'])) {
                $location = [
                    'lat' => $validated['latitude'],
                    'lng' => $validated['longitude'],
                    'accuracy' => $validated['accuracy'] ?? null,
                ];
            }

            // Check if user has a trusted contact
            $hasContact = $user->trustedContacts()
                ->where('verified', true)
                ->where('active', true)
                ->exists();

            if (!$hasContact) {
                return response()->json([
                    'success' => false,
                    'error' => 'You need at least one active verified trusted contact before checking in.',
                    'code' => 'NO_TRUSTED_CONTACT',
                ], 422);
            }

            // Create check-in using service
            $result = $this->checkInService->createCheckIn($user, $status, $location);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to create check-in.',
                    'code' => $result['code'] ?? 'CHECKIN_FAILED',
                ], 422);
            }

            // Dispatch event
            event(new CheckInCompleted($user, $result['check_in']));

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'data' => [
                    'check_in_id' => $result['check_in']->id,
                    'status' => $status,
                    'timestamp' => $result['check_in']->checked_in_at,
                    'confidence' => $result['confidence'] ?? null,
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
            Log::error('Check-in failed: ' . $e->getMessage(), [
                'user_id' => $user->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to check in: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    public function status(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 401);
            }

            $lastCheckIn = CheckIn::where('user_id', $user->id)
                ->latest()
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'last_check_in' => $lastCheckIn,
                    'has_check_in' => (bool) $lastCheckIn,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get check-in status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
