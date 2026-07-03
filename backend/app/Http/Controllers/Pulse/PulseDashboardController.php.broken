<?php

namespace App\Http\Controllers\Pulse;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Pulse\SafetyScoreService;
use App\Services\Pulse\ScoreLevels;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PulseDashboardController extends Controller
{
    protected SafetyScoreService $scoreService;
    
    public function __construct(SafetyScoreService $scoreService)
    {
        $this->scoreService = $scoreService;
    }
    
    public function dashboard(): View
    {
        $users = User::all();
        $userScores = [];
        $totalScore = 0;
        $emergencyCount = 0;
        $atRiskCount = 0;
        
        foreach ($users as $user) {
            $score = $this->scoreService->calculateScore($user);
            $level = $this->scoreService->getLevel($score);
            $levelConfig = ScoreLevels::getLevelConfig($level);
            $factors = $this->scoreService->getFactors($user);
            $trend = $this->scoreService->getTrend($user);
            
            if ($level === 'emergency') {
                $emergencyCount++;
            }
            if ($level === 'at_risk') {
                $atRiskCount++;
            }
            
            $userScores[] = [
                'user' => $user,
                'score' => $score,
                'level' => $level,
                'level_config' => $levelConfig,
                'factors' => $factors,
                'trend' => $trend
            ];
            $totalScore += $score;
        }
        
        $avgScore = $users->count() > 0 ? round($totalScore / $users->count()) : 0;
        $rules = $this->scoreService->getDetectionEngine()->getActiveRules();
        
        return view('pulse.dashboard', [
            'userScores' => $userScores,
            'avgScore' => $avgScore,
            'totalUsers' => $users->count(),
            'emergencyCount' => $emergencyCount,
            'atRiskCount' => $atRiskCount,
            'scoreLevels' => ScoreLevels::LEVELS,
            'activeRules' => $rules
        ]);
    }
    
    public function getSafetyScore(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        return response()->json([
            'score' => $this->scoreService->calculateScore($user),
            'level' => $this->scoreService->getLevel(
                $this->scoreService->calculateScore($user)
            ),
            'level_config' => ScoreLevels::getLevelConfig(
                $this->scoreService->getLevel(
                    $this->scoreService->calculateScore($user)
                )
            ),
            'factors' => $this->scoreService->getFactors($user),
            'trend' => $this->scoreService->getTrend($user)
        ]);
    }
    
    public function getScoreHistory(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        $days = $request->input('days', 7);
        
        return response()->json([
            'history' => $this->scoreService->getScoreHistory($user, $days),
            'trend' => $this->scoreService->getTrend($user)
        ]);
    }
    
    public function getMetrics(Request $request)
    {
        $users = User::all();
        $metrics = [
            'total_users' => $users->count(),
            'average_score' => 0,
            'emergency_count' => 0,
            'at_risk_count' => 0,
            'safe_count' => 0,
            'active_rules' => count($this->scoreService->getDetectionEngine()->getActiveRules())
        ];
        
        $totalScore = 0;
        foreach ($users as $user) {
            $score = $this->scoreService->calculateScore($user);
            $level = $this->scoreService->getLevel($score);
            $totalScore += $score;
            
            if ($level === 'emergency') {
                $metrics['emergency_count']++;
            } elseif ($level === 'at_risk') {
                $metrics['at_risk_count']++;
            } else {
                $metrics['safe_count']++;
            }
        }
        
        $metrics['average_score'] = $users->count() > 0 ? round($totalScore / $users->count()) : 0;
        
        return response()->json($metrics);
    }
    
    public function runDetection(Request $request)
    {
        $user = $request->user() ?? User::find($request->input('user_id'));
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $result = $this->scoreService->runSafetyCheck($user);
        return response()->json($result);
    }
    
    public function getActiveRules(Request $request)
    {
        $rules = $this->scoreService->getDetectionEngine()->getActiveRules();
        return response()->json(['rules' => $rules]);
    }
    
    public function getAllDetections(Request $request)
    {
        $detections = $this->scoreService->getDetectionEngine()->runDetectionForAllUsers();
        return response()->json(['detections' => $detections]);
    }
}
    
    public function getWidgetData(Request $request)
    {
        $users = User::all();
        $history = [];
        $totalScore = 0;
        
        foreach ($users as $user) {
            $score = $this->scoreService->calculateScore($user);
            $totalScore += $score;
            
            // Get history for first user only (for demo)
            if (empty($history)) {
                $history = $this->scoreService->getScoreHistory($user, 7);
            }
        }
        
        $avgScore = $users->count() > 0 ? round($totalScore / $users->count()) : 0;
        
        return response()->json([
            'average_score' => $avgScore,
            'total_users' => $users->count(),
            'history' => $history,
            'trend' => $this->scoreService->getTrend($users->first() ?? new \App\Models\User())
        ]);
    }
    
    public function runAutomatedCheck(Request $request)
    {
        $user = $request->user() ?? User::find($request->input('user_id'));
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $result = $this->scoreService->runSafetyCheckWithAutomation($user);
        return response()->json($result);
    }
    
    public function getIncidents(Request $request)
    {
        $incidents = \App\Models\WatchtowerIncident::where('source', 'pulse')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        return response()->json(['incidents' => $incidents]);
    }
