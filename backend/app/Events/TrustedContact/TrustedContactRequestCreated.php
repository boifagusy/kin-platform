<?php

namespace App\Events\TrustedContact;

use App\Models\TrustedContact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithBroadcasting;

class TrustedContactRequestCreated
{
    use Dispatchable, InteractsWithBroadcasting;

    public function __construct(
        public TrustedContact $contact,
        public int $initiatorUserId
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('user.' . $this->contact->user_id);
    }
}
