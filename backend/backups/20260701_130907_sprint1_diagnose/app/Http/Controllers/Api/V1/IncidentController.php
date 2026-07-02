<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SafetyIncident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    /**
     * Get all incidents for the authenticated user
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $incidents = SafetyIncident::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            $data = $incidents->map(function ($incident) {
                return [
                    'id' => $incident->id,
                    'type' => $incident->type ?? 'sos',
                    'status' => $incident->status ?? 'active',
                    'is_duress' => $incident->is_duress ?? false,
                    'description' => $incident->description ?? 'Safety incident',
                    'location' => $incident->location ? json_decode($incident->location, true) : null,
                    'confidence_score' => $incident->confidence_score ?? null,
                    'created_at' => $incident->created_at ? $incident->created_at->toISOString() : null,
                    'updated_at' => $incident->updated_at ? $incident->updated_at->toISOString() : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Incident index error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load incidents'
            ], 500);
        }
    }

    /**
     * Get a single incident by ID
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $incident = SafetyIncident::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$incident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incident not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $incident->id,
                    'type' => $incident->type ?? 'sos',
                    'status' => $incident->status ?? 'active',
                    'is_duress' => $incident->is_duress ?? false,
                    'description' => $incident->description ?? 'Safety incident',
                    'location' => $incident->location ? json_decode($incident->location, true) : null,
                    'confidence_score' => $incident->confidence_score ?? null,
                    'created_at' => $incident->created_at ? $incident->created_at->toISOString() : null,
                    'updated_at' => $incident->updated_at ? $incident->updated_at->toISOString() : null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Incident show error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'incident_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load incident'
            ], 500);
        }
    }

    /**
     * Mark an incident as resolved
     */
    public function markResolved(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $incident = SafetyIncident::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$incident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incident not found'
                ], 404);
            }

            $incident->status = 'resolved';
            $incident->save();

            return response()->json([
                'success' => true,
                'message' => 'Incident resolved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Incident resolve error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'incident_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve incident'
            ], 500);
        }
    }

    /**
     * Get notifications for a contact
     */
    public function notifications(Request $request, $phone)
    {
        try {
            $incidents = SafetyIncident::where('phone', $phone)
                ->orWhere('contact_phone', $phone)
                ->where('status', 'active')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $incidents
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load notifications'
            ], 500);
        }
    }
}
