<?php

namespace App\Services\Pulse\Rules\Detection;

use App\Models\User;
use App\Models\SafetyEvent;
use Carbon\Carbon;
use App\Services\Pulse\Rules\Base\BaseRule;

class LowBatteryEmergencyRule extends BaseRule
{
    protected int $threshold = 15;
    protected string $severity = 'high';
    
    public function detect(User $user): bool
    {
        $emergencyEvent = SafetyEvent::where('user_id', $user->id)
            ->whereIn('event_type', ['sos_triggered', 'safety_alert'])
            ->where('created_at', '>=', Carbon::now()->subHour())
            ->latest('created_at')
            ->first();
            
        if (!$emergencyEvent) {
            return false;
        }
        
        if ($emergencyEvent->battery_level !== null && 
            $emergencyEvent->battery_level <= $this->threshold) {
            $this->createEvent($user, [
                'battery_level' => $emergencyEvent->battery_level,
                'emergency_event_id' => $emergencyEvent->id
            ]);
            return true;
        }
        
        return false;
    }
    
    public function getEventType(): string
    {
        return 'low_battery_emergency';
    }
    
    public function getDescription(): string
    {
        return 'Low battery (' . $this->threshold . '%) during emergency';
    }
}
