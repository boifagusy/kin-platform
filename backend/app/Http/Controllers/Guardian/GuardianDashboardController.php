<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Services\Guardian\GuardianAggregationService;
use App\Services\Guardian\GuardianTimelineService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuardianDashboardController extends Controller
{
    protected $aggregationService;
    protected $timelineService;
    
    public function __construct(
        GuardianAggregationService $aggregationService,
        GuardianTimelineService $timelineService
    ) {
        $this->aggregationService = $aggregationService;
        $this->timelineService = $timelineService;
    }
    
    public function dashboard(): View
    {
        $platformStatus = $this->aggregationService->getPlatformStatus();
        $guardianScore = $this->aggregationService->getGuardianScore();
        $timeline = $this->timelineService->getTimeline(20);
        
        return view('guardian.dashboard', [
            'platformStatus' => $platformStatus,
            'guardianScore' => $guardianScore,
            'timeline' => $timeline,
            'lastUpdated' => now()->toIso8601String()
        ]);
    }
    
    public function apiStatus(Request $request)
    {
        $platformStatus = $this->aggregationService->getPlatformStatus();
        $guardianScore = $this->aggregationService->getGuardianScore();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'platform' => $platformStatus,
                'score' => $guardianScore,
                'timestamp' => now()->toIso8601String()
            ]
        ]);
    }
    
    public function apiTimeline(Request $request)
    {
        $limit = $request->input('limit', 50);
        $timeline = $this->timelineService->getTimeline($limit);
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'events' => $timeline,
                'count' => count($timeline),
                'timestamp' => now()->toIso8601String()
            ]
        ]);
    }
}
