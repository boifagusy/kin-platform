<?php

namespace App\Services;

use App\Models\IncidentNotification;
use App\Events\NotificationDispatched;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class NotificationService
{
    public function getAll($userId, $category = null, $type = null, $limit = 50)
    {
        $query = IncidentNotification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($category) {
            $query->where('category', $category);
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function getUnreadCount($userId)
    {
        return IncidentNotification::where('user_id', $userId)
            ->whereNull('viewed_at')
            ->count();
    }

    public function markAllRead($userId)
    {
        return IncidentNotification::where('user_id', $userId)
            ->whereNull('viewed_at')
            ->update(['viewed_at' => Carbon::now()]);
    }

    public function createIncident($data)
    {
        // N10: Idempotency check
        $idempotencyKey = $data['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $lockKey = "notification_idempotent:{$idempotencyKey}";
            if (Cache::has($lockKey)) {
                return IncidentNotification::find(Cache::get($lockKey));
            }
        }

        $notification = IncidentNotification::create([
            'user_id' => $data['user_id'],
            'type' => $data['type'] ?? 'incident',
            'title' => $data['title'],
            'body' => $data['body'],
            'category' => $data['category'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'incident_type' => $data['incident_type'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);

        // N10: Store idempotency lock
        if ($idempotencyKey) {
            Cache::put("notification_idempotent:{$idempotencyKey}", $notification->id, now()->addMinutes(5));
        }

        event(new NotificationDispatched($notification));

        return $notification;
    }
}
