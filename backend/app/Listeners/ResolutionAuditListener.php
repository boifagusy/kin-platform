<?php

namespace App\Listeners;

use App\Events\EmergencyResolved;
use Illuminate\Support\Facades\Log;

class ResolutionAuditListener
{
    /**
     * Synchronous — single DB insert, negligible latency.
     */
    public function handle(EmergencyResolved $event): void
    {
        $incident = $event->incident;

        Log::info('Incident resolved', [
            'incident_id' => $incident->id,
            'resolved_by' => $incident->resolved_by_user_id,
            'role' => $incident->resolved_by_role,
            'note' => $incident->resolution_note,
        ]);
    }
}
