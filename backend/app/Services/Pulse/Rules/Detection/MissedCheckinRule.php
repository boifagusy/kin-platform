<?php

namespace App\Services\Pulse\Rules\Detection;

use App\Models\User;
use App\Models\SafetyEvent;
use Carbon\Carbon;
use App\Services\Pulse\Rules\Base\BaseRule;

class MissedCheckinRule extends BaseRule
{
    protected int $threshold = 30;
    protected string $severity = 'medium';
    
    public function detect(User $user): bool
    {
        $lastCheckin = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', 'checkin')
            ->latest('created_at')
            ->first();
            
        if (!$lastCheckin) {
            $this->createEvent($user, ['reason' => 'no_checkin_history']);
            return true;
        }
        
        $minutesSince = Carbon::now()->diffInMinutes($lastCheckin->created_at);
        
        if ($minutesSince > $this->threshold) {
            $this->createEvent($user, [
                'minutes_since' => $minutesSince,
                'last_checkin' => $lastCheckin->created_at->toIso8601String()
            ]);
            return true;
        }
        
        return false;
    }
    
    public function getEventType(): string
    {
        return 'missed_checkin';
    }
    
    public function getDescription(): string
    {
        return 'User missed check-in for ' . $this->threshold . ' minutes';
    }
}
