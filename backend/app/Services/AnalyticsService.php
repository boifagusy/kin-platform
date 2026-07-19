<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\CampaignDelivery;
use App\Models\IncidentNotification;
use App\Models\EmergencyBroadcast;
use App\Models\Version;
use App\Models\Template;
use App\Services\Watchtower\NotificationMonitorService;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function __construct(
        private ?NotificationMonitorService $monitor = null
    ) {
        $this->monitor = $monitor ?? app(NotificationMonitorService::class);
    }

    public function getDashboardStats(): array
    {
        return [
            'announcements' => [
                'total' => Announcement::count(),
                'published' => Announcement::where('status', 'published')->count(),
                'draft' => Announcement::where('status', 'draft')->count(),
            ],
            'campaigns' => [
                'total_deliveries' => CampaignDelivery::count(),
                'sent' => CampaignDelivery::where('status', 'sent')->count(),
                'failed' => CampaignDelivery::where('status', 'failed')->count(),
                'pending' => CampaignDelivery::where('status', 'pending')->count(),
            ],
            'notifications' => [
                'total' => IncidentNotification::count(),
            ],
            'emergency_broadcasts' => [
                'total' => EmergencyBroadcast::count(),
                'active' => EmergencyBroadcast::where('status', 'active')->count(),
            ],
            'versions' => [
                'total' => Version::count(),
                'active' => Version::where('is_active', true)->count(),
            ],
            'templates' => [
                'total' => Template::count(),
                'published' => Template::where('status', 'published')->count(),
            ],
        ];
    }

    /**
     * Get notification-specific analytics
     */
    public function getNotificationAnalytics(): array
    {
        $total = IncidentNotification::count();
        $delivered = IncidentNotification::where('status', 'delivered')->count();
        $failed = IncidentNotification::where('status', 'failed')->count();
        $pending = IncidentNotification::where('status', 'pending')->count();
        $read = IncidentNotification::whereNotNull('viewed_at')->count();
        $unread = $total - $read;

        return [
            'total' => $total,
            'delivered' => $delivered,
            'failed' => $failed,
            'pending' => $pending,
            'read' => $read,
            'unread' => $unread,
            'delivery_rate' => $total > 0 ? round(($delivered / $total) * 100, 1) : 0,
            'read_rate' => $total > 0 ? round(($read / $total) * 100, 1) : 0,
            'monitor' => $this->monitor->getMetrics(),
        ];
    }

    /**
     * Get notification trends (daily/weekly/monthly)
     */
    public function getNotificationTrends(string $period = 'daily'): array
    {
        $days = match ($period) {
            'weekly' => 7,
            'monthly' => 30,
            default => 7,
        };

        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'sent' => IncidentNotification::whereDate('created_at', $date)->count()
                    + CampaignDelivery::whereDate('created_at', $date)->count(),
                'delivered' => IncidentNotification::whereDate('delivered_at', $date)->where('status', 'delivered')->count()
                    + CampaignDelivery::whereDate('sent_at', $date)->where('status', 'sent')->count(),
                'failed' => IncidentNotification::whereDate('created_at', $date)->where('status', 'failed')->count()
                    + CampaignDelivery::whereDate('created_at', $date)->where('status', 'failed')->count(),
                'read' => IncidentNotification::whereDate('viewed_at', $date)->count(),
            ];
        }

        return [
            'period' => $period,
            'days' => $days,
            'data' => $data,
        ];
    }

    /**
     * Get channel breakdown
     */
    public function getChannelBreakdown(): array
    {
        $channels = ['push', 'sms', 'email', 'whatsapp'];
        $result = [];

        foreach ($channels as $channel) {
            $incidents = IncidentNotification::where('delivery_channel', $channel);
            $campaigns = CampaignDelivery::where('channel', $channel);

            $result[$channel] = [
                'sent' => (clone $incidents)->count() + (clone $campaigns)->count(),
                'delivered' => (clone $incidents)->where('status', 'delivered')->count()
                    + (clone $campaigns)->where('status', 'sent')->count(),
                'failed' => (clone $incidents)->where('status', 'failed')->count()
                    + (clone $campaigns)->where('status', 'failed')->count(),
                'pending' => (clone $incidents)->where('status', 'pending')->count()
                    + (clone $campaigns)->where('status', 'pending')->count(),
            ];
        }

        return ['channels' => $result];
    }

    /**
     * Get failure summary
     */
    public function getFailureSummary(): array
    {
        $incidentFailures = IncidentNotification::where('status', 'failed')->get();
        $campaignFailures = CampaignDelivery::where('status', 'failed')->get();

        $reasons = [];
        foreach ($campaignFailures as $f) {
            $reason = $f->error ?: 'Unknown';
            $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
        }
        foreach ($incidentFailures as $f) {
            $reason = 'Incident delivery failure';
            $reasons[$reason] = ($reasons[$reason] ?? 0) + 1;
        }
        arsort($reasons);

        $byChannel = [];
        foreach (['push', 'sms', 'email', 'whatsapp'] as $ch) {
            $count = IncidentNotification::where('delivery_channel', $ch)->where('status', 'failed')->count()
                + CampaignDelivery::where('channel', $ch)->where('status', 'failed')->count();
            if ($count > 0) {
                $byChannel[$ch] = $count;
            }
        }

        return [
            'total_failures' => count($incidentFailures) + count($campaignFailures),
            'top_reasons' => array_slice(array_map(
                fn($k, $v) => ['reason' => $k, 'count' => $v],
                array_keys($reasons), $reasons
            ), 0, 10),
            'by_channel' => $byChannel,
        ];
    }

    /**
     * Search and filter notifications
     */
    public function searchNotifications(array $filters = []): array
    {
        $query = IncidentNotification::query()->orderBy('created_at', 'desc');

        if (!empty($filters['user_id'])) {
            $query->whereHas('incident', fn($q) => $q->where('user_id', $filters['user_id']));
        }
        if (!empty($filters['channel'])) {
            $query->where('delivery_channel', $filters['channel']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $perPage = min($filters['per_page'] ?? 20, 100);
        $results = $query->paginate($perPage);

        return [
            'data' => $results->items(),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'total' => $results->total(),
                'per_page' => $perPage,
                'last_page' => $results->lastPage(),
            ],
        ];
    }

    /**
     * Retry a single failed notification
     */
    public function retryNotification(int $id): array
    {
        $notification = IncidentNotification::findOrFail($id);

        if ($notification->status !== 'failed') {
            return ['success' => false, 'message' => 'Only failed notifications can be retried'];
        }

        $notification->update(['status' => 'pending']);
        // Queue would pick this up in production

        return ['success' => true, 'new_status' => 'pending', 'id' => $id];
    }

    /**
     * Bulk retry failed notifications
     */
    public function retryBulk(array $ids): array
    {
        $retried = 0;
        $failed = 0;

        foreach ($ids as $id) {
            $result = $this->retryNotification($id);
            if ($result['success']) {
                $retried++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => true,
            'retried_count' => $retried,
            'failed_count' => $failed,
        ];
    }
}
