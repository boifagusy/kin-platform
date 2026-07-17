<?php
namespace App\Services;
use App\Models\IncidentNotification;
use App\Models\CampaignDelivery;
use Illuminate\Support\Collection;

class NotificationFeedService
{
    public function getFeed(int $userId, int $page = 1, int $perPage = 20): array
    {
        $incidents = $this->getIncidentNotifications($userId);
        $campaigns = $this->getCampaignNotifications($userId);

        $all = $incidents->merge($campaigns)
            ->sortByDesc('created_at')
            ->values();

        $unreadCount = $all->where('read', false)->count();
        $total = $all->count();
        $offset = ($page - 1) * $perPage;
        $items = $all->slice($offset, $perPage)->values();

        return [
            'data' => $items->toArray(),
            'unread_count' => $unreadCount,
            'page' => $page,
            'has_more' => ($offset + $perPage) < $total,
        ];
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->getIncidentNotifications($userId)
            ->merge($this->getCampaignNotifications($userId))
            ->where('read', false)
            ->count();
    }

    private function getIncidentNotifications(int $userId): Collection
    {
        return IncidentNotification::whereHas('incident', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
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
            'source' => 'incident_notification',
        ]);
    }

    private function getCampaignNotifications(int $userId): Collection
    {
        return CampaignDelivery::where('user_id', $userId)
            ->with('campaign')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($d) => [
                'id' => 'campaign_' . $d->id,
                'type' => 'campaign',
                'title' => $d->campaign->title ?? 'Push Campaign',
                'message' => $d->campaign->body ?? '',
                'created_at' => $d->created_at?->toISOString(),
                'read' => $d->status === 'sent',
                'source' => 'campaign_delivery',
            ]);
    }
}
