<?php

namespace App\Services\Sentinel;

use App\Models\User;
use App\Models\SecurityEvent;
use Carbon\Carbon;

class RiskEngine
{
    public function getSecurityStatus(): array
    {
        try {
            $recentEvents = SecurityEvent::where('created_at', '>=', Carbon::now()->subHours(24))
                ->count();
                
            $highRiskUsers = User::where('is_locked', 1)->count();
            $totalUsers = User::count();
            
            $riskLevel = 'normal';
            $score = 95;
            
            if ($highRiskUsers > 0) {
                $riskLevel = 'elevated';
                $score = 80;
            }
            
            if ($recentEvents > 50) {
                $riskLevel = 'high';
                $score = 70;
            }
            
            return [
                'level' => $riskLevel,
                'score' => $score,
                'high_risk_users' => $highRiskUsers,
                'recent_events' => $recentEvents,
                'total_users' => $totalUsers,
                'threats' => []
            ];
        } catch (\Exception $e) {
            return [
                'level' => 'unknown',
                'score' => 0,
                'error' => $e->getMessage(),
                'threats' => []
            ];
        }
    }
}
