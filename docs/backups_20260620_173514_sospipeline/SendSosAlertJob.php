<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\SosEvent;
use App\Models\SafetyIncident;
use App\Models\TrustedContact;
use App\Models\IncidentNotification;
use App\Models\ActivityLog;
use App\Models\EmergencyEscalation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSosAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $userId;
    protected $sosId;

    public function __construct($userId, $sosId)
    {
        $this->userId = $userId;
        $this->sosId = $sosId;
    }

    public function handle(): void
    {
        $user = User::find($this->userId);
        $sos = SosEvent::find($this->sosId);

        if (!$user || !$sos) {
            Log::error('SendSosAlertJob: User or SOS not found', [
                'user_id' => $this->userId,
                'sos_id' => $this->sosId
            ]);
            return;
        }

        // 1. Create Emergency Escalation
        $escalation = EmergencyEscalation::create([
            'user_id' => $user->id,
            'escalation_type' => 'sos',
            'status' => 'active',
            'priority' => 'critical',
            'location_lat' => $sos->latitude,
            'location_lng' => $sos->longitude,
            'notes' => 'SOS alert triggered via queue',
        ]);

        // 2. Create Safety Incident
        $incident = SafetyIncident::create([
            'user_id' => $user->id,
            'type' => 'sos',
            'status' => 'active',
            'location_lat' => $sos->latitude,
            'location_lng' => $sos->longitude,
            'location_accuracy' => $sos->accuracy ?? null,
            'battery_level' => $sos->battery_level ?? null,
            'message' => "Emergency SOS triggered by {$user->name}",
            'escalated_at' => now(),
        ]);

        Log::info('Safety incident created', [
            'incident_id' => $incident->id,
            'user_id' => $user->id,
        ]);

        // 3. Fetch active trusted contacts
        $contacts = TrustedContact::where('user_id', $user->id)
            ->where('verified', true)
            ->where('active', true)
            ->get();

        Log::info('Trusted contacts found', [
            'user_id' => $user->id,
            'count' => $contacts->count(),
        ]);

        // 4. Create notifications for each contact
        foreach ($contacts as $contact) {
            IncidentNotification::create([
                'incident_id' => $incident->id,
                'trusted_contact_id' => $contact->id,
                'delivery_channel' => 'in_app',
                'status' => 'pending',
                'message' => "{$user->name} may need assistance. Tap to view location.",
            ]);

            Log::info('Notification created for contact', [
                'incident_id' => $incident->id,
                'trusted_contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'contact_phone' => $contact->phone,
            ]);
        }

        // 5. Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'type' => 'SOS_NOTIFICATION_CREATED',
            'status' => 'success',
            'details' => json_encode([
                'incident_id' => $incident->id,
                'escalation_id' => $escalation->id,
                'contacts_notified' => $contacts->count(),
                'sos_id' => $sos->id,
            ]),
            'occurred_at' => now(),
        ]);

        Log::info('SOS alert processed successfully', [
            'user_id' => $user->id,
            'sos_id' => $sos->id,
            'incident_id' => $incident->id,
            'contacts_notified' => $contacts->count(),
        ]);
    }
}
