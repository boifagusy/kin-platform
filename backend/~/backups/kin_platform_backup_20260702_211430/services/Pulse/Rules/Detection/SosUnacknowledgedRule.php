<?php

namespace App\Services\Pulse\Rules\Detection;

use App\Models\User;
use App\Models\SafetyEvent;
use Carbon\Carbon;
use App\Services\Pulse\Rules\Base\BaseRule;

class SosUnacknowledgedRule extends BaseRule
{
    protected int $threshold = 5;
    protected string $severity = 'critical';
    
    public function detect(User $user): bool
    {
        $sosEvent = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', 'sos_triggered')
            ->whereNull('guardian_acknowledged_at')
            ->latest('created_at')
            ->first();
            
        if (!$sosEvent) {
            return false;
        }
        
        $minutesSince = Carbon::now()->diffInMinutes($sosEvent->created_at);
        
        if ($minutesSince > $this->threshold) {
            $this->createEvent($user, [
                'sos_event_id' => $sosEvent->id,
                'minutes_unacknowledged' => $minutesSince
            ]);
            return true;
        }
        
        return false;
    }
    
    public function getEventType(): string
    {
        return 'sos_unacknowledged';
    }
    
    public function getDescription(): string
    {
        return 'SOS unacknowledged for ' . $this->threshold . ' minutes';
    }
}
