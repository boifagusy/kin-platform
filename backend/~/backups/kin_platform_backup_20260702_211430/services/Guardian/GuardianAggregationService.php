<?php

namespace App\Services\Guardian;

use App\Services\Watchtower\DashboardAggregationService;
use App\Services\Sentinel\RiskEngine;
use App\Services\Pulse\SafetyScoreService;
use App\Models\User;
use App\Models\WatchtowerIncident;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GuardianAggregationService
{
    protected $watchtowerService;
    protected $sentinelEngine;
    protected $pulseService;
    
    public function __construct(
        DashboardAggregationService $watchtowerService,
        RiskEngine $sentinelEngine,
        SafetyScoreService $pulseService
    ) {
        $this->watchtowerService = $watchtowerService;
        $this->sentinelEngine = $sentinelEngine;
        $this->pulseService = $pulseService;
    }
    
    public function getPlatformStatus(): array
    {
        $health = $this->getHealthStatus();
        $security = $this->getSecurityStatus();
        $safety = $this->getSafetyStatus();
        $incidents = $this->getIncidentSummary();
        
        return [
            'health' => $health,
            'security' => $security,
            'safety' => $safety,
            'incidents' => $incidents,
            'timestamp' => Carbon::now()->toIso8601String()
        ];
    }
    
    protected function getHealthStatus(): array
    {
        try {
            $health = $this->watchtowerService->getPlatformHealth();
            return [
                'status' => $health['status'] ?? 'healthy',
                'score' => $health['score'] ?? 95,
                'issues' => $health['issues'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Watchtower health check failed: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'score' => 0,
                'issues' => ['Watchtower unavailable']
            ];
        }
    }
    
    protected function getSecurityStatus(): array
    {
        try {
            $security = $this->sentinelEngine->getSecurityStatus();
            return [
                'status' => $security['level'] ?? 'normal',
                'score' => $security['score'] ?? 89,
                'threats' => $security['threats'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Sentinel security check failed: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'score' => 0,
                'threats' => ['Sentinel unavailable']
            ];
        }
    }
    
    protected function getSafetyStatus(): array
    {
        try {
            $safety = $this->pulseService->getSafetyStatus();
            return [
                'status' => $safety['level'] ?? 'normal',
                'score' => $safety['score'] ?? 94,
                'emergencies' => $safety['emergencies'] ?? []
            ];
        } catch (\Exception $e) {
            Log::error('Pulse safety check failed: ' . $e->getMessage());
            return [
                'status' => 'unknown',
                'score' => 0,
                'emergencies' => ['Pulse unavailable']
            ];
        }
    }
    
    protected function getIncidentSummary(): array
    {
        try {
            $incidents = WatchtowerIncident::where('status', '!=', 'resolved')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
                
            return [
                'total' => $incidents->count(),
                'critical' => $incidents->where('severity', 'critical')->count(),
                'high' => $incidents->where('severity', 'high')->count(),
                'list' => $incidents->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Incident summary failed: ' . $e->getMessage());
            return [
                'total' => 0,
                'critical' => 0,
                'high' => 0,
                'list' => []
            ];
        }
    }
    
    public function getGuardianScore(): array
    {
        $health = $this->getHealthStatus();
        $security = $this->getSecurityStatus();
        $safety = $this->getSafetyStatus();
        
        $operations = min(100, max(0, $health['score'] ?? 95));
        $securityScore = min(100, max(0, $security['score'] ?? 89));
        $safetyScore = min(100, max(0, $safety['score'] ?? 94));
        
        $overall = round(
            ($operations * 0.3) +
            ($securityScore * 0.35) +
            ($safetyScore * 0.35)
        );
        
        return [
            'overall' => min(100, max(0, $overall)),
            'operations' => $operations,
            'security' => $securityScore,
            'safety' => $safetyScore,
            'level' => $this->getScoreLevel($overall)
        ];
    }
    
    protected function getScoreLevel(int $score): string
    {
        if ($score >= 80) return 'excellent';
        if ($score >= 60) return 'good';
        if ($score >= 40) return 'fair';
        return 'critical';
    }
}
