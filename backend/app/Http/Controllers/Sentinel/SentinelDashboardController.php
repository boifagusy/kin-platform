<?php

namespace App\Http\Controllers\Sentinel;

use App\Http\Controllers\Controller;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\Sentinel\RiskEngine;
use App\Services\Sentinel\DetectionEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SentinelDashboardController extends Controller
{
    protected $riskEngine;
    protected $detectionEngine;

    public function __construct()
    {
        $this->riskEngine = new RiskEngine();
        $this->detectionEngine = new DetectionEngine();
    }

    public function index()
    {
        $metrics = $this->getMetrics();
        $threats = $this->getActiveThreats();
        $highRiskUsers = $this->getHighRiskUsers();
        $timeline = $this->getSecurityTimeline();

        return view('sentinel.dashboard', compact('metrics', 'threats', 'highRiskUsers', 'timeline'));
    }

    public function metrics(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getMetrics(),
        ]);
    }

    public function threats(Request $request)
    {
        $threats = $this->getActiveThreats();
        return response()->json([
            'success' => true,
            'data' => $threats,
        ]);
    }

    public function highRiskUsers(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getHighRiskUsers(),
        ]);
    }

    public function timeline(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSecurityTimeline(),
        ]);
    }

    public function charts(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getChartData(),
        ]);
    }

    protected function getMetrics(): array
    {
        $today = now()->startOfDay();
        $totalEvents = SecurityEvent::count();
        $todayEvents = SecurityEvent::where('created_at', '>=', $today)->count();
        $criticalEvents = SecurityEvent::where('severity', 'critical')->count();
        $failedLogins = SecurityEvent::where('event_type', 'login_pin_failed')->count();

        $score = $this->calculateSecurityScore();
        $lockedAccounts = User::where('is_locked', true)->count();

        return [
            'security_score' => $score,
            'active_threats' => $criticalEvents,
            'critical_threats' => $criticalEvents,
            'high_risk_users' => $this->getHighRiskUsersCount(),
            'locked_accounts' => $lockedAccounts,
            'security_events_24h' => $todayEvents,
            'mttd' => '2.5 min',
            'mttr' => '15.8 min',
            'timestamp' => now()->toISOString(),
        ];
    }

    protected function calculateSecurityScore(): int
    {
        $totalEvents = SecurityEvent::count();
        if ($totalEvents === 0) {
            return 100;
        }

        $criticalEvents = SecurityEvent::where('severity', 'critical')->count();
        $failedLogins = SecurityEvent::where('event_type', 'login_pin_failed')->count();

        $score = 100;
        $score -= min(30, $criticalEvents * 3);
        $score -= min(20, $failedLogins * 0.5);

        return max(0, min(100, (int)$score));
    }

    protected function getHighRiskUsersCount(): int
    {
        $users = User::all();
        $count = 0;
        foreach ($users as $user) {
            $score = $this->riskEngine->calculateScore($user->id);
            if ($score > 40) {
                $count++;
            }
        }
        return $count;
    }

    protected function getActiveThreats(): array
    {
        $threats = SecurityEvent::where('severity', 'critical')
            ->orWhere('severity', 'high')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $threats->map(function ($event) {
            $user = $event->user_id ? User::find($event->user_id) : null;
            $riskScore = $event->user_id ? $this->riskEngine->calculateScore($event->user_id) : 0;

            return [
                'id' => $event->id,
                'time' => $event->created_at->toISOString(),
                'threat_type' => $event->event_type,
                'severity' => $event->severity,
                'user' => $user ? $user->name : 'Unknown',
                'user_id' => $event->user_id,
                'device' => 'Unknown',
                'ip' => $event->source_ip ?? 'Unknown',
                'risk_score' => $riskScore,
                'status' => $this->determineStatus($event),
                'action_taken' => $event->details['action_taken'] ?? 'Logged',
                'details' => $event->details,
            ];
        })->toArray();
    }

    protected function determineStatus($event): string
    {
        if ($event->resolved_at) {
            return 'Resolved';
        }
        if ($event->details['action_taken'] ?? false) {
            return 'Mitigated';
        }
        if ($event->severity === 'critical') {
            return 'Investigating';
        }
        return 'New';
    }

    protected function getHighRiskUsers(): array
    {
        $users = User::all();
        $highRiskUsers = [];

        foreach ($users as $user) {
            $score = $this->riskEngine->calculateScore($user->id);
            if ($score > 40) {
                $failedLogins = SecurityEvent::where('user_id', $user->id)
                    ->where('event_type', 'login_pin_failed')
                    ->count();
                $lastActivity = SecurityEvent::where('user_id', $user->id)
                    ->latest()
                    ->first();

                $highRiskUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'risk_score' => $score,
                    'risk_level' => $this->riskEngine->getRiskLevel($score)['label'],
                    'failed_logins' => $failedLogins,
                    'last_activity' => $lastActivity ? $lastActivity->created_at->toISOString() : null,
                    'recommended_action' => $this->getRecommendedAction($score),
                ];
            }
        }

        usort($highRiskUsers, function ($a, $b) {
            return $b['risk_score'] - $a['risk_score'];
        });

        return array_slice($highRiskUsers, 0, 20);
    }

    protected function getRecommendedAction(int $score): string
    {
        if ($score >= 70) {
            return 'Lock Account Immediately';
        }
        if ($score >= 50) {
            return 'Require OTP Verification';
        }
        if ($score >= 40) {
            return 'Monitor Activity';
        }
        return 'No Action Needed';
    }

    protected function getSecurityTimeline(): array
    {
        $events = SecurityEvent::orderBy('created_at', 'desc')->limit(50)->get();

        return $events->map(function ($event) {
            $user = $event->user_id ? User::find($event->user_id) : null;
            return [
                'id' => $event->id,
                'timestamp' => $event->created_at->toISOString(),
                'event_type' => $event->event_type,
                'severity' => $event->severity,
                'user' => $user ? $user->name : 'Unknown',
                'details' => $event->details,
                'action' => $event->details['action_taken'] ?? 'Logged',
            ];
        })->toArray();
    }

    protected function getChartData(): array
    {
        $days = 7;
        $dailyData = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->addDay();

            $failedLogins = SecurityEvent::where('event_type', 'login_pin_failed')
                ->where('created_at', '>=', $date)
                ->where('created_at', '<', $nextDate)
                ->count();

            $otpRequests = SecurityEvent::where('event_type', 'otp_requested')
                ->where('created_at', '>=', $date)
                ->where('created_at', '<', $nextDate)
                ->count();

            $critical = SecurityEvent::where('severity', 'critical')
                ->where('created_at', '>=', $date)
                ->where('created_at', '<', $nextDate)
                ->count();

            $dailyData[] = [
                'date' => $date->format('Y-m-d'),
                'failed_logins' => $failedLogins,
                'otp_requests' => $otpRequests,
                'critical' => $critical,
                'total' => $failedLogins + $otpRequests + $critical,
            ];
        }

        $severityData = [
            ['name' => 'Critical', 'value' => SecurityEvent::where('severity', 'critical')->count()],
            ['name' => 'High', 'value' => SecurityEvent::where('severity', 'high')->count()],
            ['name' => 'Medium', 'value' => SecurityEvent::where('severity', 'medium')->count()],
            ['name' => 'Low', 'value' => SecurityEvent::where('severity', 'low')->count()],
        ];

        return [
            'daily_trend' => $dailyData,
            'severity_breakdown' => $severityData,
        ];
    }
}
