<?php

namespace App\Listeners;

use App\Events\SOSTriggered;
use App\Jobs\SendSosAlertJob;

class QueueSosAlert
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SOSTriggered $event): void
    {
        $location = null;
        if ($event->sosEvent->latitude && $event->sosEvent->longitude) {
            $location = [
                'lat' => $event->sosEvent->latitude,
                'lng' => $event->sosEvent->longitude,
                'accuracy' => $event->sosEvent->accuracy,
            ];
        }

        SendSosAlertJob::dispatch(
            $event->user,
            $location,
            $event->sosEvent->is_duress ?? false
        );
    }
}
