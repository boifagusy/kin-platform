<?php

namespace App\Services\Watchtower;

use Illuminate\Support\Facades\DB;
use App\Models\CheckIn;
use App\Models\SosEvent;

class SafetyEngineMonitorService
{
    /**
     * Get safety engine metrics
     */
    public function getMetrics(): array
    {
        return [
            'checkins' => $this->getCheckinMetrics(),
            'sos' => $this->getSosMetrics(),
            'duress' => $this->getDuressMetrics(),
            'background_tracking' => $this->getBackgroundTrackingStatus(),
            'confidence_engine' => $this->getConfidenceEngineStatus(),
            'overall_health' => $this->calculateOverallHealth(),
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get check-in metrics
     */
    protected function getCheckinMetrics(): array
    {
        try {
            $totalToday = CheckIn::whereDate('checked_in_at', today())->count();
            $totalWeek = CheckIn::where('checked_in_at', '>=', now()->subDays(7))->count();
            $lastCheckin = CheckIn::latest('checked_in_at')->first();

            return [
                'total_today' => $totalToday,
                'total_week' => $totalWeek,
                'last_checkin' => $lastCheckin?->checked_in_at,
                'status' => $totalToday > 0 ? 'healthy' : 'warning',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get SOS metrics
     */
    protected function getSosMetrics(): array
    {
        try {
            $totalToday = SosEvent::whereDate('triggered_at', today())->count();
            $totalWeek = SosEvent::where('triggered_at', '>=', now()->subDays(7))->count();
            $activeSos = SosEvent::whereNull('resolved_at')->count();
            $lastSos = SosEvent::latest('triggered_at')->first();

            return [
                'total_today' => $totalToday,
                'total_week' => $totalWeek,
                'active_sos' => $activeSos,
                'last_sos' => $lastSos?->triggered_at,
                'status' => $activeSos > 0 ? 'warning' : 'healthy',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get duress metrics
     */
    protected function getDuressMetrics(): array
    {
        try {
            $duressEvents = SosEvent::where('is_duress', true)
                ->where('triggered_at', '>=', now()->subDays(7))
                ->count();

            return [
                'duress_events' => $duressEvents,
                'status' => 'healthy',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get background tracking status
     */
    protected function getBackgroundTrackingStatus(): array
    {
        // In production, this would check if background tracking is active
        return [
            'status' => 'healthy',
            'is_active' => true,
        ];
    }

    /**
     * Get confidence engine status
     */
    protected function getConfidenceEngineStatus(): array
    {
        // In production, this would check the confidence engine
        return [
            'status' => 'healthy',
            'score' => 85,
        ];
    }

    /**
     * Calculate overall health
     */
    protected function calculateOverallHealth(): array
    {
        $statuses = [
            $this->getCheckinMetrics()['status'] ?? 'healthy',
            $this->getSosMetrics()['status'] ?? 'healthy',
            $this->getDuressMetrics()['status'] ?? 'healthy',
            $this->getBackgroundTrackingStatus()['status'] ?? 'healthy',
            $this->getConfidenceEngineStatus()['status'] ?? 'healthy',
        ];

        $critical = array_filter($statuses, function ($status) {
            return $status === 'critical';
        });

        $unhealthy = array_filter($statuses, function ($status) {
            return $status === 'unhealthy';
        });

        if (count($critical) > 0) {
            $status = 'critical';
            $score = 30;
        } elseif (count($unhealthy) > 0) {
            $status = 'warning';
            $score = 60;
        } else {
            $status = 'healthy';
            $score = 100;
        }

        return [
            'status' => $status,
            'score' => $score,
        ];
    }
}
