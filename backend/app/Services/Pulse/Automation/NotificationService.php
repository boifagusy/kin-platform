<?php

namespace App\Services\Pulse\Automation;

use App\Models\User;
use App\Models\SafetyEvent;

class NotificationService
{
    public function notifyGuardian(User $user, SafetyEvent $event): void
    {
        // Guardian notification logic
        \Log::info('Guardian notified', [
            'user_id' => $user->id,
            'event_type' => $event->event_type
        ]);
    }
}
