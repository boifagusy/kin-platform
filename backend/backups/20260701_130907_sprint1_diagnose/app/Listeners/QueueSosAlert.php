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
        SendSosAlertJob::dispatch(
            $event->user->id,
            $event->sosEvent->id
        );
    }
}
