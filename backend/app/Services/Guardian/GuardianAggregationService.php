<?php

namespace App\Services\Guardian;

use App\Models\WatchtowerIncident;
use App\Models\User;
use Carbon\Carbon;

class GuardianAggregationService
{
    public function getPlatformStatus(): array
    {
        $incidents = WatchtowerIncident::where('status', '!=', 'resolved')->count();
        $totalUsers = User::count();
        
        return [
            'health' => [
                'status' => $incidents > 0 ? 'degraded' : 'healthy',
                'score' => $incidents > 0 ? 80 : 95,
                'issues' => []
            ],
            'security' => [
                'status' => 'normal',
                'score' => 89,
                'threats' => []
            ],
            'safety' => [
                'status' => 'normal',
                'score' => 94,
                'emergencies' => []
            ],
            'incidents' => [
                'total' => $incidents,
                'critical' => WatchtowerIncident::where('severity', 'critical')->where('status', '!=', 'resolved')->count(),
                'high' => WatchtowerIncident::where('severity', 'high')->where('status', '!=', 'resolved')->count(),
                'list' => WatchtowerIncident::where('status', '!=', 'resolved')->orderBy('created_at', 'desc')->take(10)->get()->toArray()
            ],
            'timestamp' => Carbon::now()->toIso8601String()
        ];
    }

    public function getGuardianScore(): array
    {
        $health = $this->getPlatformStatus();
        
        $operations = $health['health']['score'] ?? 95;
        $security = $health['security']['score'] ?? 89;
        $safety = $health['safety']['score'] ?? 94;
        
        $overall = round(($operations * 0.3) + ($security * 0.35) + ($safety * 0.35));
        
        return [
            'overall' => min(100, max(0, $overall)),
            'operations' => $operations,
            'security' => $security,
            'safety' => $safety,
            'level' => $overall >= 80 ? 'excellent' : ($overall >= 60 ? 'good' : ($overall >= 40 ? 'fair' : 'critical'))
        ];
    }
}
