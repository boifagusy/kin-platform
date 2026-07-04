<?php

namespace App\Http\Controllers\Pulse;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Pulse\SafetyScoreService;
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
        $safeCount = 0;

        foreach ($users as $user) {
            try {
                $score = $this->scoreService->calculateScore($user);
                $level = $this->scoreService->getLevel($score);
                $levelConfig = $this->getLevelConfig($level);
                $factors = $this->scoreService->getFactors($user);
                $trend = $this->scoreService->getTrend($user);

                if ($level === 'emergency') {
                    $emergencyCount++;
                } elseif ($level === 'at_risk') {
                    $atRiskCount++;
                } else {
                    $safeCount++;
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
            } catch (\Exception $e) {
                // Skip users with errors
            }
        }

        $avgScore = $users->count() > 0 ? round($totalScore / $users->count()) : 0;

        // Get recent safety events
        $recentEvents = collect();
        try {
            $recentEvents = \App\Models\SafetyEvent::with('user')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        } catch (\Exception $e) {
            // Table might not exist
        }

        return view('pulse.dashboard', [
            'userScores' => $userScores,
            'avgScore' => $avgScore,
            'totalUsers' => $users->count(),
            'emergencyCount' => $emergencyCount,
            'atRiskCount' => $atRiskCount,
            'safeCount' => $safeCount,
            'recentEvents' => $recentEvents,
            'scoreLevels' => $this->getScoreLevels(),
        ]);
    }

    protected function getLevelConfig(string $level): array
    {
        $levels = $this->getScoreLevels();
        return $levels[$level] ?? $levels['safe'];
    }

    protected function getScoreLevels(): array
    {
        return [
            'safe' => [
                'label' => 'Safe',
                'icon' => '🟢',
                'min_score' => 80,
                'max_score' => 100,
                'color' => 'green'
            ],
            'monitor' => [
                'label' => 'Monitor',
                'icon' => '🟡',
                'min_score' => 50,
                'max_score' => 79,
                'color' => 'yellow'
            ],
            'at_risk' => [
                'label' => 'At Risk',
                'icon' => '🟠',
                'min_score' => 30,
                'max_score' => 49,
                'color' => 'orange'
            ],
            'emergency' => [
                'label' => 'Emergency',
                'icon' => '🔴',
                'min_score' => 0,
                'max_score' => 29,
                'color' => 'red'
            ]
        ];
    }

    public function getSafetyScore(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'score' => $this->scoreService->calculateScore($user),
            'level' => $this->scoreService->getLevel($this->scoreService->calculateScore($user)),
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
}
