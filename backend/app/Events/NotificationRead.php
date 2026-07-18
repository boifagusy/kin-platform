<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class NotificationRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $action,
        public ?string $notificationId,
        public int $badgeCount,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('notifications.' . $this->userId);
    }

    public function broadcastWith(): array
    {
        return [
            'event_version' => '1.0',
            'event_id' => Str::uuid()->toString(),
            'action' => $this->action,
            'notification_id' => $this->notificationId,
            'user_id' => $this->userId,
            'badge_count' => $this->badgeCount,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
