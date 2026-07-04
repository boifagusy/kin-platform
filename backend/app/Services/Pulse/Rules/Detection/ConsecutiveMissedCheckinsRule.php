<?php

namespace App\Services\Pulse\Rules\Detection;

use App\Models\User;
use App\Models\SafetyEvent;
use Carbon\Carbon;
use App\Services\Pulse\Rules\Base\BaseRule;

class ConsecutiveMissedCheckinsRule extends BaseRule
{
    protected int $threshold = 2;
    protected string $severity = 'high';
    
    public function detect(User $user): bool
    {
        $missedCount = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', 'missed_checkin')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->count();
            
        if ($missedCount >= $this->threshold) {
            $this->createEvent($user, [
                'missed_count' => $missedCount,
                'threshold' => $this->threshold
            ]);
            return true;
        }
        
        return false;
    }
    
    public function getEventType(): string
    {
        return 'consecutive_missed_checkins';
    }
    
    public function getDescription(): string
    {
        return $this->threshold . ' or more consecutive missed check-ins';
    }
}
