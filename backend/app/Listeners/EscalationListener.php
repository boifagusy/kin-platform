<?php

namespace App\Listeners;

use App\Events\EmergencyTriggered;
use App\Models\EmergencyEscalation;

class EscalationListener
{
    /**
     * Synchronous — escalation begins immediately after incident commit.
     * Intentionally not in the incident transaction.
     */
    public function handle(EmergencyTriggered $event): void
    {
        $incident = $event->incident;

        EmergencyEscalation::create([
            'user_id' => $incident->user_id,
            'escalation_type' => 'sos',
            'status' => EmergencyEscalation::STATUS_PENDING,
            'priority' => 'high',
        ]);
    }
}
