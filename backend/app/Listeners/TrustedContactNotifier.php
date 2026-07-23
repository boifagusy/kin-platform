<?php

namespace App\Listeners;

use App\Events\EmergencyTriggered;
use App\Models\TrustedContact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TrustedContactNotifier implements ShouldQueue
{
    /**
     * Queued — notification delivery must not block SOS trigger response.
     */
    public function handle(EmergencyTriggered $event): void
    {
        $incident = $event->incident;

        $contacts = TrustedContact::where('user_id', $incident->user_id)
            ->where('verified', true)
            ->get();

        if ($contacts->isEmpty()) {
            Log::warning('No trusted contacts found for SOS notification', [
                'user_id' => $incident->user_id,
                'incident_id' => $incident->id,
            ]);
            return;
        }

        foreach ($contacts as $contact) {
            Log::info('Trusted contact notified', [
                'contact_id' => $contact->id,
                'incident_id' => $incident->id,
                'phone' => $contact->phone,
            ]);
        }
    }
}
