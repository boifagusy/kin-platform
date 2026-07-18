<?php
namespace App\Services;

use App\Models\IncidentNotification;
use App\Models\CampaignDelivery;
use App\Models\Announcement;
use Illuminate\Support\Collection;

class NotificationService
{
    public function getUnifiedFeed(int $userId, int $page = 1, int $perPage = 20): array
    {
        $items = collect()
            ->merge($this->getIncidents($userId))
            ->merge($this->getCampaigns($userId))
            ->merge($this->getAnnouncements())
            ->sortByDesc('created_at')
            ->values();

        $unread = $items->where('read', false)->count();
        $offset = ($page - 1) * $perPage;

        return [
            'data' => $items->slice($offset, $perPage)->values()->toArray(),
            'unread_count' => $unread,
            'total' => $items->count(),
            'page' => $page,
            'has_more' => ($offset + $perPage) < $items->count(),
        ];
    }

    private function getIncidents(int $userId): Collection
    {
        return IncidentNotification::whereHas('incident', fn($q) => $q->where('user_id', $userId))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($n) => [
                'id' => 'incident_' . $n->id,
                'type' => 'incident',
                'title' => 'Safety Alert',
                'message' => $n->message ?? 'Incident notification',
                'created_at' => $n->created_at?->toISOString(),
                'read' => !is_null($n->viewed_at),
                'category' => 'security',
            ]);
    }

    private function getCampaigns(int $userId): Collection
    {
        return CampaignDelivery::where('user_id', $userId)
            ->with('campaign')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($d) => [
                'id' => 'campaign_' . $d->id,
                'type' => 'campaign',
                'title' => $d->campaign->title ?? 'Campaign',
                'message' => $d->campaign->body ?? '',
                'created_at' => $d->created_at?->toISOString(),
                'read' => $d->status === 'sent',
                'category' => 'marketing',
                'channel' => $d->channel ?? 'push',
            ]);
    }

    private function getAnnouncements(): Collection
    {
        return Announcement::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id' => 'announcement_' . $a->id,
                'type' => 'announcement',
                'title' => $a->title,
                'message' => $a->message,
                'created_at' => $a->created_at?->toISOString(),
                'read' => false,
                'category' => 'system',
            ]);
    }
}

    public function markRead(string $id, int $userId): void
    {
        if (str_starts_with($id, 'incident_')) {
            $realId = substr($id, 9);
            IncidentNotification::where('id', $realId)->update(['viewed_at' => now()]);
        }
        // Campaign + Announcement read state handled via their own lifecycle
    }

    public function markAllRead(int $userId): void
    {
        IncidentNotification::whereHas('incident', fn($q) => $q->where('user_id', $userId))
            ->whereNull('viewed_at')
            ->update(['viewed_at' => now()]);
    }

    public function getBadgeCount(int $userId): int
    {
        return $this->getUnifiedFeed($userId)['unread_count'] ?? 0;
    }
