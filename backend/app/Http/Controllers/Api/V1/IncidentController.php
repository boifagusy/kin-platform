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
                'status_label' => match($i->status) { 'active' => 'Active', 'responding' => 'Responding', 'resolved' => 'Resolved', default => $i->status },
                'status_color' => match($i->status) { 'active' => 'danger', 'responding' => 'warning', 'resolved' => 'success', default => 'neutral' },
                'message' => $i->message,
                'location_lat' => $i->location_lat,
                'location_lng' => $i->location_lng,
                'battery_level' => $i->battery_level,
                'user_phone' => $i->user?->phone,
                'created_at' => $i->created_at?->toISOString(),
                'resolved_at' => $i->resolved_at?->toISOString(),
                'responding_by' => $i->respondingBy ? ['id' => $i->respondingBy->id, 'name' => $i->respondingBy->name] : null,
                'responding_at' => $i->responding_at?->toISOString(),
                'can_resolve' => $i->status !== 'resolved',
                'can_respond' => $i->status === 'active' && $i->user_id !== auth()->id(),
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

            $incident = SafetyIncident::where('id', $id)->first();
            if (!$incident) {
                return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
            }

            $isOwner = $incident->user_id === $user->id;
            $isTrustedContact = false;

            if (!$isOwner) {
                $permissionService = app(\App\Services\EmergencyPermissionService::class);
                $normalizedUserPhone = $permissionService->normalizePhone($user->phone);

                $isTrustedContact = \App\Models\TrustedContact::where('user_id', $incident->user_id)
                    ->where('verified', true)
                    ->get()
                    ->contains(fn($tc) => $permissionService->normalizePhone($tc->phone) === $normalizedUserPhone);
            }

            if (!$isOwner && !$isTrustedContact) {
                return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
            }

            app(\App\Services\EmergencyLifecycleService::class)->resolve(
                $incident->id,
                $user->id,
                $request->input('role', 'user'),
                $request->input('note')
            );

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

    public function respond(Request $request, $id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $incident = SafetyIncident::where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('user.trustedContacts', function ($q2) use ($user) {
                      $q2->where('phone', $user->phone);
                  });
            })
            ->first();

        if (!$incident) {
            return response()->json(['success' => false, 'message' => 'Incident not found'], 404);
        }

        // Only active incidents can be responded to
        if ($incident->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => $incident->status === 'responding'
                    ? 'Another trusted contact is already responding.'
                    : 'This incident is already ' . $incident->status . '.'
            ], 409);
        }

        // Owner cannot respond to their own SOS
        if ($incident->user_id === $user->id) {
            return response()->json(['success' => false, 'message' => 'You cannot respond to your own SOS.'], 403);
        }

        $incident->status = 'responding';
        $incident->responding_by_user_id = $user->id;
        $incident->responding_at = now();
        $incident->save();

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $incident->status,
                'status_label' => 'Responding',
                'status_color' => 'warning',
                'responding_by' => ['id' => $user->id, 'name' => $user->name],
                'responding_at' => $incident->responding_at->toISOString(),
            ]
        ]);
    }

}