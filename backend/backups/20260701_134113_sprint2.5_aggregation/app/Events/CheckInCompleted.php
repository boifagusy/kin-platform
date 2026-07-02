<?php

namespace App\Events;

use App\Models\User;
use App\Models\CheckIn;
use Illuminate\Foundation\Events\Dispatchable;

class CheckInCompleted
{
    use Dispatchable;
    
    public User $user;
    public CheckIn $checkIn;
    
    public function __construct(User $user, CheckIn $checkIn)
    {
        $this->user = $user;
        $this->checkIn = $checkIn;
    }
}
