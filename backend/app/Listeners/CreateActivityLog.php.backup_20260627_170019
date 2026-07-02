<?php

namespace App\Listeners;

use App\Events\CheckInCompleted;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateActivityLog implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(CheckInCompleted $event)
    {
        ActivityLog::create([
            'user_id' => $event->checkIn->user_id,
            'type' => 'check_in',
            'status' => $event->checkIn->status,
            'details' => [
                'check_in_id' => $event->checkIn->id,
                'latitude' => $event->checkIn->latitude,
                'longitude' => $event->checkIn->longitude,
            ],
            'occurred_at' => now(),
        ]);
    }
}
