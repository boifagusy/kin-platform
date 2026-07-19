<?php

namespace App\Services\Watchtower;

use App\Models\IncidentNotification;
use App\Models\CampaignDelivery;
use Illuminate\Support\Facades\DB;

class NotificationMonitorService
{
    /**
     * Get notification metrics
     */
    public function getMetrics(): array
    {
        return [
            'sms' => $this->getSmsMetrics(),
            'push' => $this->getPushMetrics(),
            'email' => $this->getEmailMetrics(),
            'retries' => $this->getRetryMetrics(),
            'failures' => $this->getFailureMetrics(),
            'delivery_rate' => $this->getDeliveryRate(),
            'queue' => $this->getQueueStats(),
            'retry_stats' => $this->getRetryStats(),
            'overall_health' => $this->calculateOverallHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get SMS metrics from incident_notifications
     */
    protected function getSmsMetrics(): array
    {
        $base = IncidentNotification::where('delivery_channel', 'sms');

        return [
            'sent_today' => (clone $base)->whereDate('created_at', today())->count(),
            'sent_week' => (clone $base)->where('created_at', '>=', now()->subWeek())->count(),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get Push metrics from incident_notifications + campaign_deliveries
     */
    protected function getPushMetrics(): array
    {
        $incidents = IncidentNotification::where('delivery_channel', 'push');
        $campaigns = CampaignDelivery::where('channel', 'push');

        return [
            'sent_today' => (clone $incidents)->whereDate('created_at', today())->count()
                + (clone $campaigns)->whereDate('created_at', today())->count(),
            'sent_week' => (clone $incidents)->where('created_at', '>=', now()->subWeek())->count()
                + (clone $campaigns)->where('created_at', '>=', now()->subWeek())->count(),
            'delivered' => (clone $incidents)->where('status', 'delivered')->count()
                + (clone $campaigns)->where('status', 'sent')->count(),
            'failed' => (clone $incidents)->where('status', 'failed')->count()
                + (clone $campaigns)->where('status', 'failed')->count(),
            'pending' => (clone $incidents)->where('status', 'pending')->count()
                + (clone $campaigns)->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get Email metrics
     */
    protected function getEmailMetrics(): array
    {
        $base = IncidentNotification::where('delivery_channel', 'email');

        return [
            'sent_today' => (clone $base)->whereDate('created_at', today())->count(),
            'sent_week' => (clone $base)->where('created_at', '>=', now()->subWeek())->count(),
            'delivered' => (clone $base)->where('status', 'delivered')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get retry metrics from campaign_deliveries
     */
    protected function getRetryMetrics(): array
    {
        $retried = CampaignDelivery::whereNotNull('sent_at')
            ->where('status', '!=', 'pending')
            ->where('updated_at', '>', DB::raw('COALESCE(sent_at, created_at)'))
            ->count();

        $totalFailed = CampaignDelivery::where('status', 'failed')->count()
            + IncidentNotification::where('status', 'failed')->count();

        return [
            'total_retried' => $retried,
            'retry_candidates' => $totalFailed,
            'retry_rate' => $totalFailed > 0 ? round(($retried / $totalFailed) * 100, 1) : 0,
        ];
    }

    /**
     * Get failure metrics
     */
    protected function getFailureMetrics(): array
    {
        $incidentFailures = IncidentNotification::where('status', 'failed')->count();
        $campaignFailures = CampaignDelivery::where('status', 'failed')->count();

        return [
            'total_failures' => $incidentFailures + $campaignFailures,
            'incident_failures' => $incidentFailures,
            'campaign_failures' => $campaignFailures,
            'today_failures' => IncidentNotification::where('status', 'failed')->whereDate('created_at', today())->count()
                + CampaignDelivery::where('status', 'failed')->whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get delivery rate
     */
    public function getDeliveryRate(): array
    {
        $totalIncidents = IncidentNotification::count();
        $deliveredIncidents = IncidentNotification::where('status', 'delivered')->count();

        $totalCampaigns = CampaignDelivery::count();
        $deliveredCampaigns = CampaignDelivery::where('status', 'sent')->count();

        $total = $totalIncidents + $totalCampaigns;
        $delivered = $deliveredIncidents + $deliveredCampaigns;

        return [
            'total' => $total,
            'delivered' => $delivered,
            'rate' => $total > 0 ? round(($delivered / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get queue statistics
     */
    public function getQueueStats(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            $processing = DB::table('jobs')->whereNotNull('reserved_at')->count();

            return [
                'pending' => $pending,
                'processing' => $processing,
                'failed' => $failed,
                'total' => $pending + $failed,
                'status' => $failed > 50 ? 'warning' : ($pending > 500 ? 'busy' : 'healthy'),
            ];
        } catch (\Exception $e) {
            return [
                'pending' => 0,
                'processing' => 0,
                'failed' => 0,
                'total' => 0,
                'status' => 'error',
            ];
        }
    }

    /**
     * Get retry statistics
     */
    public function getRetryStats(): array
    {
        $retryAttempts = CampaignDelivery::whereNotNull('sent_at')
            ->where('status', '!=', 'pending')
            ->whereColumn('updated_at', '>', 'sent_at')
            ->count();

        $successfulRetries = CampaignDelivery::where('status', 'sent')
            ->whereNotNull('sent_at')
            ->whereColumn('updated_at', '>', 'sent_at')
            ->count();

        $failedJobs = DB::table('failed_jobs')->count();

        return [
            'total_retries' => $retryAttempts,
            'successful_retries' => $successfulRetries,
            'failed_retries' => $failedJobs,
            'success_rate' => $retryAttempts > 0 ? round(($successfulRetries / $retryAttempts) * 100, 1) : 0,
        ];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $delivery = $this->getDeliveryRate();
        $queue = $this->getQueueStats();
        $failures = $this->getFailureMetrics();

        $score = 100;
        $status = 'healthy';

        if ($delivery['rate'] < 80) {
            $status = 'critical';
            $score -= 40;
        } elseif ($delivery['rate'] < 95) {
            $status = 'warning';
            $score -= 20;
        }

        if ($queue['failed'] > 100) {
            $status = 'critical';
            $score -= 30;
        } elseif ($queue['failed'] > 50) {
            $status = 'warning';
            $score -= 15;
        }

        if ($failures['total_failures'] > 500) {
            $status = 'critical';
            $score -= 30;
        } elseif ($failures['total_failures'] > 200) {
            $status = 'warning';
            $score -= 15;
        }

        return [
            'status' => $status,
            'score' => max(0, $score),
            'delivery_rate' => $delivery['rate'],
            'failed_jobs' => $queue['failed'],
        ];
    }
}
