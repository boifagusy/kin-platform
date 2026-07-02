<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\SafetyIncident;
use App\Models\IncidentNotification;
use App\Models\TrustedContact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $incidents = SafetyIncident::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'incidents' => $incidents,
                'total' => $incidents->count(),
                'unread' => $incidents->where('status', 'active')->count(),
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $incident = SafetyIncident::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('notifications')
            ->first();

        if (!$incident) {
            return response()->json(['success' => false, 'error' => 'Incident not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $incident
        ]);
    }
    public function markResolved(Request $request, $id)
    {
        $incident = SafetyIncident::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$incident) {
            return response()->json(['success' => false, 'error' => 'Incident not found'], 404);
        }

        $incident->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Incident resolved'
        ]);
    }

    public function notifications($contactPhone)
    {
        Log::info('Trusted contact notifications called', ['phone' => $contactPhone]);

        // Try multiple formats, and match ALL contacts with this phone
        $contacts = TrustedContact::where('phone', $contactPhone)
            ->orWhere('phone', "'" . $contactPhone . "'")
            ->orWhere('phone', str_replace("'", "", $contactPhone))
            ->get();

        if ($contacts->isEmpty()) {
            Log::warning('Trusted contact not found', ['phone' => $contactPhone]);
            return response()->json(['success' => false, 'error' => 'Contact not found'], 404);
        }

        $contactIds = $contacts->pluck('id');
        Log::info('Trusted contacts found', ['contact_ids' => $contactIds]);

        $notifications = IncidentNotification::whereIn('trusted_contact_id', $contactIds)
            ->whereNull('viewed_at')
            ->with('incident')
            ->get();

        Log::info('Notifications found', ['count' => $notifications->count()]);

        // Mark as viewed now that they have been delivered to this client
        IncidentNotification::whereIn('id', $notifications->pluck('id'))
            ->update(['viewed_at' => now()]);
        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }
}

    /**
     * Get incidents with confidence scores
     */
    public function indexWithScores(Request $request)
    {
        $user = $request->user();
        
        $incidents = SafetyIncident::where('user_id', $user->id)
            ->with(['escalation', 'notifications'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        $scoreService = app(\App\Services\SafetyScoreService::class);
        $currentScore = $scoreService->getForUser($user);
        
        return response()->json([
            'current_confidence' => $currentScore,
            'current_tier' => $scoreService->getTier($currentScore),
            'incidents' => $incidents->map(function ($incident) use ($scoreService) {
                return [
                    'id' => $incident->id,
                    'type' => $incident->type,
                    'status' => $incident->status,
                    'is_duress' => $incident->is_duress,
                    'confidence_at_time' => $incident->confidence_score ?? null,
                    'created_at' => $incident->created_at,
                    'escalation_status' => $incident->escalation->status ?? null,
                    'notifications' => $incident->notifications->count(),
                ];
            }),
        ]);
    }
