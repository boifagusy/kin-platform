<?php

namespace App\Events;

use App\Models\User;
use App\Models\SosEvent;
use Illuminate\Foundation\Events\Dispatchable;

class SOSTriggered
{
    use Dispatchable;
    
    public User $user;
    public SosEvent $sosEvent;
    
    public function __construct(User $user, SosEvent $sosEvent)
    {
        $this->user = $user;
        $this->sosEvent = $sosEvent;
    }
}
