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
        $phone = $request->query('phone');

        if (!$phone) {
            return response()->json(['success' => false, 'error' => 'Phone required'], 400);
        }

        $user = \App\Models\User::where('phone', $phone)
            ->orWhere('phone', "'" . $phone . "'")
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found'], 404);
        }

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

    public function show($id)
    {
        $incident = SafetyIncident::with('notifications')->find($id);

        if (!$incident) {
            return response()->json(['success' => false, 'error' => 'Incident not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $incident
        ]);
    }

    public function markResolved($id)
    {
        $incident = SafetyIncident::find($id);

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

        // Try multiple formats
        $contact = TrustedContact::where('phone', $contactPhone)
            ->orWhere('phone', "'" . $contactPhone . "'")
            ->orWhere('phone', str_replace("'", "", $contactPhone))
            ->first();

        if (!$contact) {
            Log::warning('Trusted contact not found', ['phone' => $contactPhone]);
            return response()->json(['success' => false, 'error' => 'Contact not found'], 404);
        }

        Log::info('Trusted contact found', ['contact_id' => $contact->id]);

        $notifications = IncidentNotification::where('trusted_contact_id', $contact->id)
            ->where('status', 'pending')
            ->with('incident')
            ->get();

        Log::info('Notifications found', ['count' => $notifications->count()]);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }
}
