<?php

namespace App\Events\TrustedContact;

use App\Models\TrustedContact;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TrustedContactRequestCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $recipientUserId;

    public function __construct(
        public TrustedContact $contact,
        public int $initiatorUserId,
    ) {
        $recipient = User::where('phone', $this->contact->phone)->first();
        $this->recipientUserId = $recipient?->id ?? 0;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('notifications.' . $this->recipientUserId);
    }

    public function broadcastWith(): array
    {
        return [
            'event_version' => '1.0',
            'event_id' => Str::uuid()->toString(),
            'contact_id' => $this->contact->id,
            'initiator_id' => $this->initiatorUserId,
            'contact_name' => $this->contact->name,
            'contact_phone' => $this->contact->phone,
            'message' => $this->contact->name . ' wants to add you as a Trusted Contact.',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
