<?php

namespace App\Listeners;

use App\Events\CheckInCompleted;
use App\Events\SOSTriggered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class RefreshDashboardCache implements ShouldQueue
{
    public function handle($event)
    {
        $userId = $event->checkIn->user_id ?? $event->sos->user_id ?? null;
        
        if ($userId) {
            Cache::forget("dashboard_{$userId}");
            Cache::forget("activities_{$userId}");
        }
    }
}
