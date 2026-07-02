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
        ]);

        $user = $request->user();

        $user->last_location = json_encode([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'timestamp' => $request->timestamp ?? time(),
        ]);
        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'LOCATION_UPDATE',
            'status' => 'recorded',
            'details' => json_encode([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'accuracy' => $request->accuracy,
                'speed' => $request->speed,
                'heading' => $request->heading,
            ]),
            'occurred_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated',
        ]);
    }
}
