<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $lastLocation = ActivityLog::where('user_id', $user->id)
            ->where('type', 'LOCATION_UPDATE')
            ->latest()
            ->first();

        if (!$lastLocation) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No location data available',
            ]);
        }

        $details = json_decode($lastLocation->details, true);

        return response()->json([
            'success' => true,
            'data' => [
                'latitude' => $details['latitude'] ?? null,
                'longitude' => $details['longitude'] ?? null,
                'accuracy' => $details['accuracy'] ?? null,
                'speed' => $details['speed'] ?? null,
                'heading' => $details['heading'] ?? null,
                'timestamp' => $lastLocation->occurred_at,
                'age' => now()->diffInSeconds($lastLocation->occurred_at),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'timestamp' => 'nullable|integer',
            'device_trust' => 'nullable|numeric',
            'device_fingerprint' => 'nullable|string',
            'battery_level' => 'nullable|integer',
            'is_charging' => 'nullable|boolean',
            'network_type' => 'nullable|string',
            'confidence' => 'nullable|numeric',
        ]);

        $user = $request->user();

        $user->last_location = json_encode([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'timestamp' => $request->timestamp ?? time(),
            'device_trust' => $request->device_trust,
            'battery_level' => $request->battery_level,
            'network_type' => $request->network_type,
            'confidence' => $request->confidence,
        ]);
        $user->save();

        // Flag suspicious activity based on device trust
        $isSuspicious = ($request->device_trust !== null && $request->device_trust < 50) ||
                        ($request->confidence !== null && $request->confidence < 30);

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'LOCATION_UPDATE',
            'status' => $isSuspicious ? 'suspicious' : 'recorded',
            'details' => json_encode([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'device_trust' => $request->device_trust,
                'device_fingerprint' => $request->device_fingerprint,
                'battery_level' => $request->battery_level,
                'is_charging' => $request->is_charging,
                'network_type' => $request->network_type,
                'confidence' => $request->confidence,
                'is_suspicious' => $isSuspicious,
            ]),
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated',
        ]);
    }
}
