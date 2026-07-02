<?php

namespace App\Listeners;

use App\Events\CheckInCompleted;
use App\Events\SOSTriggered;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateActivityLog implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event)
    {
        try {
            $userId = null;
            $type = null;
            $status = null;
            $details = [];

            // Handle CheckInCompleted event
            if ($event instanceof CheckInCompleted && isset($event->checkIn)) {
                $checkIn = $event->checkIn;
                $userId = $checkIn->user_id;
                $type = 'check_in';
                $status = $checkIn->status ?? 'completed';
                $details = [
                    'check_in_id' => $checkIn->id,
                    'latitude' => $checkIn->latitude ?? null,
                    'longitude' => $checkIn->longitude ?? null,
                ];
            }

            // Handle SOSTriggered event
            if ($event instanceof SOSTriggered) {
                // Try to get user from sos relationship
                if (isset($event->sos) && isset($event->sos->user_id)) {
                    $userId = $event->sos->user_id;
                }
                // Fallback: try user property
                elseif (isset($event->user_id)) {
                    $userId = $event->user_id;
                }
                
                $type = 'sos_triggered';
                $status = 'active';
                $details = [
                    'sos_id' => $event->sos->id ?? null,
                    'is_duress' => $event->sos->is_duress ?? false,
                    'location' => $event->location ?? null,
                ];
            }

            if (!$userId) {
                Log::warning('CreateActivityLog: No user ID found', [
                    'event_type' => get_class($event)
                ]);
                return;
            }

            ActivityLog::create([
                'user_id' => $userId,
                'type' => $type,
                'status' => $status,
                'details' => $details,
                'occurred_at' => now(),
            ]);

            Log::info('Activity log created', [
                'user_id' => $userId,
                'type' => $type,
                'event_type' => get_class($event)
            ]);

        } catch (\Exception $e) {
            Log::error('CreateActivityLog failed: ' . $e->getMessage(), [
                'event_type' => get_class($event)
            ]);
        }
    }
}
