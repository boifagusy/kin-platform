<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LocationTracking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocationTrackingController extends Controller
{
    /**
     * Store location tracking data
     */
    public function store(Request $request)
    {
        try {
            // Try to find user by phone first
            $user = null;
            $phone = $request->input('phone');

            if ($phone) {
                $user = User::where('phone', $phone)->first();
            }

            // If not found by phone, try authenticated user
            if (!$user) {
                $user = $request->user();
            }

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found',
                    'code' => 'USER_NOT_FOUND',
                ], 404);
            }

            // Validate request
            $validated = $request->validate([
                'phone' => 'sometimes|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'accuracy' => 'nullable|numeric|min:0',
                'speed' => 'nullable|numeric|min:0',
                'heading' => 'nullable|numeric|min:0|max:360',
                'timestamp' => 'nullable|numeric',
                'provider' => 'nullable|string|in:gps,network,fused,cell,wifi,cached,emergency',
                'battery_level' => 'nullable|integer|min:0|max:100',
                'is_background' => 'nullable|boolean',
            ]);

            // Create tracking record
            $tracking = LocationTracking::create([
                'user_id' => $user->id,
                'phone' => $user->phone,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy' => $validated['accuracy'] ?? null,
                'speed' => $validated['speed'] ?? null,
                'heading' => $validated['heading'] ?? null,
                'provider' => $validated['provider'] ?? 'gps',
                'battery_level' => $validated['battery_level'] ?? null,
                'is_background' => $validated['is_background'] ?? false,
                'tracked_at' => $validated['timestamp'] 
                    ? date('Y-m-d H:i:s', $validated['timestamp'] / 1000) 
                    : now(),
            ]);

            Log::info('Location tracked for user: ' . $user->id, [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy' => $validated['accuracy'] ?? null,
                'is_background' => $validated['is_background'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location tracked successfully',
                'data' => [
                    'id' => $tracking->id,
                    'tracked_at' => $tracking->tracked_at,
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
            Log::error('Location tracking failed: ' . $e->getMessage(), [
                'phone' => $request->input('phone'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to track location: ' . $e->getMessage(),
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Get location history for a user
     */
    public function history(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 401);
            }

            $limit = $request->input('limit', 50);
            $hours = $request->input('hours', 24);

            $locations = LocationTracking::where('user_id', $user->id)
                ->where('tracked_at', '>', now()->subHours($hours))
                ->orderBy('tracked_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'locations' => $locations,
                    'count' => $locations->count(),
                    'hours' => $hours,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get location history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get last known location for a user
     */
    public function last(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized',
                ], 401);
            }

            $last = LocationTracking::where('user_id', $user->id)
                ->latest('tracked_at')
                ->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'location' => $last,
                    'has_location' => (bool) $last,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get last location: ' . $e->getMessage(),
            ], 500);
        }
    }
}
