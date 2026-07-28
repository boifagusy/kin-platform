<?php

namespace App\Events\TrustedContact;

use App\Models\TrustedContact;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TrustedContactRemoved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TrustedContact $contact,
        public int $initiatorUserId,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('notifications.' . $this->contact->user_id);
    }

    public function broadcastWith(): array
    {
        return [
            'event_version' => '1.0',
            'event_id' => Str::uuid()->toString(),
            'contact_id' => $this->contact->id,
            'contact_name' => $this->contact->name,
            'message' => $this->contact->name . ' removed you as a trusted contact.',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
