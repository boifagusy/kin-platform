<?php

namespace App\Services\Watchtower;

use App\Models\WatchtowerIncident;
use Carbon\Carbon;

class DashboardAggregationService
{
    public function getPlatformHealth(): array
    {
        try {
            $incidents = WatchtowerIncident::where('status', '!=', 'resolved')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->count();
                
            $pendingAlerts = WatchtowerIncident::where('status', 'open')
                ->where('severity', 'critical')
                ->count();
            
            $status = 'healthy';
            $score = 95;
            
            if ($incidents > 0) {
                $status = 'degraded';
                $score = 80;
            }
            
            if ($pendingAlerts > 0) {
                $status = 'critical';
                $score = 60;
            }
            
            return [
                'status' => $status,
                'score' => $score,
                'incidents' => $incidents,
                'pending_alerts' => $pendingAlerts,
                'issues' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'score' => 0,
                'issues' => ['Watchtower unavailable']
            ];
        }
    }
    
    public function getActiveIncidents(): array
    {
        return WatchtowerIncident::where('status', '!=', 'resolved')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->toArray();
    }
}
