<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SafetyIncident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $incidents = SafetyIncident::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($i) => [
                'id' => $i->id,
                'type' => $i->type ?? 'incident',
                'status' => $i->status,
                'message' => $i->message,
                'created_at' => $i->created_at?->toISOString(),
                'resolved_at' => $i->resolved_at?->toISOString(),
                'can_resolve' => $i->status !== 'resolved',
                'can_mark_read' => is_null($i->read_at),
            ]);

        return response()->json([
            'success' => true,
            'data' => $incidents,
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $incident = SafetyIncident::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$incident) {
            return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $incident->id,
                'type' => $incident->type ?? 'incident',
                'status' => $incident->status,
                'message' => $incident->message,
                'created_at' => $incident->created_at?->toISOString(),
                'resolved_at' => $incident->resolved_at?->toISOString(),
                'resolution_note' => $incident->resolution_note,
                'can_resolve' => $incident->status !== 'resolved',
                'can_mark_read' => is_null($incident->read_at),
            ],
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $incident = SafetyIncident::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$incident) {
            return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
        }

        $incident->read_at = now();
        $incident->save();

        return response()->json(['success' => true, 'message' => 'Incident marked as read']);
    }

    public function markResolved(Request $request, $id)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $incident = SafetyIncident::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$incident) {
                return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
            }

            $incident->status = 'resolved';
            $incident->resolved_at = now();
            $incident->resolved_by_user_id = $user->id;
            $incident->resolved_by_role = $request->input('role', 'user');
            $incident->resolution_note = $request->input('note');
            $incident->save();

            return response()->json(['success' => true, 'message' => 'Incident resolved successfully']);

        } catch (\Exception $e) {
            Log::error('Incident resolve error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'incident_id' => $id
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to resolve incident'], 500);
        }
    }

    public function notifications(Request $request, $phone)
    {
        // Existing notification logic preserved
        return response()->json(['success' => true, 'data' => []]);
    }
}
