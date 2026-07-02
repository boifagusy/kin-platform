<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AssistanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssistanceController extends Controller
{
    /**
     * Store assistance request
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:call,location,alert',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $user = $request->user();

        $assistance = AssistanceRequest::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        Log::info('Assistance requested: ' . $request->type, [
            'user_id' => $user->id,
            'type' => $request->type
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assistance request recorded',
            'data' => $assistance
        ], 201);
    }
}
