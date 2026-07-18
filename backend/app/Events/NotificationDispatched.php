<?php

namespace App\Events;

use App\Models\IncidentNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationDispatched implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public IncidentNotification $notification;

    public function __construct(IncidentNotification $notification)
    {
        $this->notification = $notification;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('notifications.' . $this->notification->user_id);
    }

    public function broadcastWith(): array
    {
        $body = $this->notification->body;
        $truncated = mb_strlen($body) > 200 ? mb_substr($body, 0, 197) . '...' : $body;

        return [
            'event_version' => '1.0',
            'notification_id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'body' => $truncated,
            'category' => $this->notification->category,
            'action_url' => $this->notification->action_url,
            'created_at' => $this->notification->created_at->toIso8601String(),
            'badge_count' => \App\Models\IncidentNotification::where('user_id', $this->notification->user_id)
                ->whereNull('read_at')
                ->count(),
        ];
    }
}
