<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\SafeZone;
use Illuminate\Http\Request;

class SafeZoneController extends Controller
{
    public function index(Request $request)
    {
        $zones = SafeZone::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return ApiResponse::success(['safe_zones' => $zones], 'Safe zones retrieved');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:1|max:100',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $zone = SafeZone::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'active' => true,
        ]);

        return ApiResponse::success($zone, 'Safe zone added', 201);
    }

    public function destroy(Request $request, $id)
    {
        $zone = SafeZone::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$zone) {
            return ApiResponse::notFound('Safe zone not found');
        }

        $zone->delete();

        return ApiResponse::success(null, 'Safe zone removed');
    }
}
