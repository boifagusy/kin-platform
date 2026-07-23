<?php

namespace App\Listeners;

use App\Events\EmergencyTriggered;
use App\Jobs\SendSosAlertJob;

class QueueSosAlert
{
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EmergencyTriggered $event): void
    {
        $incident = $event->incident;

        $location = null;
        if ($incident->location_lat && $incident->location_lng) {
            $location = [
                'lat' => $incident->location_lat,
                'lng' => $incident->location_lng,
            ];
        }

        SendSosAlertJob::dispatch(
            $incident->user,
            $location,
            false
        );
    }
}
