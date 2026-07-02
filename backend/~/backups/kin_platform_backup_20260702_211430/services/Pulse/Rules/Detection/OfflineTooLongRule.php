<?php

namespace App\Services\Pulse\Rules\Detection;

use App\Models\User;
use App\Models\SafetyEvent;
use Carbon\Carbon;
use App\Services\Pulse\Rules\Base\BaseRule;

class OfflineTooLongRule extends BaseRule
{
    protected int $threshold = 120;
    protected string $severity = 'medium';
    
    public function detect(User $user): bool
    {
        $lastOnline = SafetyEvent::where('user_id', $user->id)
            ->where('network_status', 'online')
            ->latest('created_at')
            ->first();
            
        if (!$lastOnline) {
            $this->createEvent($user, ['reason' => 'no_online_history']);
            return true;
        }
        
        $minutesSince = Carbon::now()->diffInMinutes($lastOnline->created_at);
        
        if ($minutesSince > $this->threshold) {
            $this->createEvent($user, [
                'minutes_offline' => $minutesSince,
                'last_online' => $lastOnline->created_at->toIso8601String()
            ]);
            return true;
        }
        
        return false;
    }
    
    public function getEventType(): string
    {
        return 'offline_too_long';
    }
    
    public function getDescription(): string
    {
        return 'Offline for ' . $this->threshold . ' minutes';
    }
}
