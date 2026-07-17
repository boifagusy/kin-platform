<?php
namespace App\Services;
use App\Models\Announcement;
use App\Models\CampaignDelivery;
use App\Models\IncidentNotification;
use App\Models\EmergencyBroadcast;
use App\Models\Version;

class AnalyticsService
{
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
        ];
    }
}
