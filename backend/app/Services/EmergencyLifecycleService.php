<?php

namespace App\Services;

use App\Models\SafetyIncident;
use App\Models\SosEvent;
use Illuminate\Support\Facades\DB;

class EmergencyLifecycleService
{
    /**
     * Trigger a new emergency incident.
     * Dispatches EmergencyTriggered (v1) after commit.
     */
    public function trigger(int $userId, array $data): SafetyIncident
    {
        $incident = DB::transaction(function () use ($userId, $data) {
            $incident = SafetyIncident::create([
                'user_id' => $userId,
                'type' => $data['type'] ?? 'sos_triggered',
                'status' => 'active',
                'message' => $data['message'] ?? null,
                'location_lat' => $data['location_lat'] ?? null,
                'location_lng' => $data['location_lng'] ?? null,
                'battery_level' => $data['battery_level'] ?? null,
                'silent' => $data['silent'] ?? false,
            ]);

            if (($data['requires_sos_event'] ?? false) === true) {
                SosEvent::create([
                    'user_id' => $userId,
                    'safety_incident_id' => $incident->id,
                    'type' => $data['type'] ?? 'sos',
                    'status' => 'active',
                    'message' => $data['message'] ?? null,
                ]);
            }

            return $incident;
        });

        event(new \App\Events\EmergencyTriggered($incident));

        return $incident;
    }

    /**
     * Resolve an emergency incident.
     * Dispatches EmergencyResolved (v1) after commit.
     */
    public function resolve(int $incidentId, int $resolverId, string $role, ?string $note = null): SafetyIncident
    {
        $incident = DB::transaction(function () use ($incidentId, $resolverId, $role, $note) {
            $incident = SafetyIncident::findOrFail($incidentId);

            $incident->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by_user_id' => $resolverId,
                'resolved_by_role' => $role,
                'resolution_note' => $note,
            ]);

            return $incident;
        });

        event(new \App\Events\EmergencyResolved($incident));

        return $incident;
    }
}
