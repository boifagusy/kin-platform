<?php

namespace App\Services\Pulse;

use App\Models\User;
use App\Models\SafetyEvent;
use App\Models\SafetyScore;
use Carbon\Carbon;

class SafetyScoreService
{
    protected array $weights;
    protected int $decayRate;
    protected int $historyDays;
    protected DetectionEngine $detectionEngine;
    protected AutomationEngine $automationEngine;
    
    public function __construct()
    {
        $this->weights = config('pulse.score_weights', [
            'missed_checkin' => 20,
            'missed_checkin_long' => 30,
            'sos_triggered' => 40,
            'sos_unacknowledged' => 40,
            'low_battery' => 10,
            'low_battery_emergency' => 25,
            'offline_long' => 15,
            'offline_too_long' => 15,
            'guardian_unresponsive' => 15,
            'consecutive_missed_checkins' => 30,
        ]);
        $this->decayRate = config('pulse.decay_rate', 5);
        $this->historyDays = config('pulse.history_days', 7);
        $this->detectionEngine = new DetectionEngine();
        $this->automationEngine = new AutomationEngine();
    }
    
    public function calculateScore(User $user): int
    {
        $score = 100;
        $appliedImpacts = [];
        
        $events = SafetyEvent::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->get();
            
        foreach ($events as $event) {
            $impact = $this->getEventImpact($event);
            
            if (in_array($event->event_type, $appliedImpacts)) {
                continue;
            }
            
            if ($event->event_type === 'missed_checkin' && 
                in_array('consecutive_missed_checkins', $appliedImpacts)) {
                continue;
            }
            
            $appliedImpacts[] = $event->event_type;
            $score -= $impact;
        }
        
        $decay = min($this->calculateDecay($user), 20);
        $score -= $decay;
        
        return max(0, $score);
    }
    
    protected function calculateDecay(User $user): int
    {
        $lastCheckin = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', 'checkin')
            ->latest('created_at')
            ->first();
            
        if ($lastCheckin) {
            $hoursSince = Carbon::now()->diffInHours($lastCheckin->created_at);
            return min($hoursSince * $this->decayRate, 50);
        }
        
        return 10;
    }
    
    public function getLevel(int $score): string
    {
        return ScoreLevels::getLevel($score);
    }
    
    public function getFactors(User $user): array
    {
        $factors = [];
        $processedTypes = [];
        
        $events = SafetyEvent::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->get();
            
        foreach ($events as $event) {
            if (in_array($event->event_type, $processedTypes)) {
                continue;
            }
            
            if ($event->event_type === 'missed_checkin' && 
                in_array('consecutive_missed_checkins', $processedTypes)) {
                continue;
            }
            
            $processedTypes[] = $event->event_type;
            $factors[] = [
                'label' => $event->event_type,
                'impact' => $this->getEventImpact($event),
                'time' => $event->created_at->diffForHumans()
            ];
        }
        
        $decay = min($this->calculateDecay($user), 20);
        if ($decay > 0) {
            $factors[] = [
                'label' => 'score_decay',
                'impact' => $decay,
                'time' => 'active'
            ];
        }
        
        return $factors;
    }
    
    public function getScoreHistory(User $user, ?int $days = null): array
    {
        $days = $days ?? $this->historyDays;
        
        return SafetyScore::where('user_id', $user->id)
            ->where('calculated_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('calculated_at', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'score' => $record->score,
                    'level' => $record->level,
                    'time' => $record->calculated_at->toIso8601String()
                ];
            })
            ->toArray();
    }
    
    public function getTrend(User $user): string
    {
        $history = $this->getScoreHistory($user, 1);
        
        if (count($history) < 2) {
            return 'stable';
        }
        
        $first = $history[0]['score'];
        $last = end($history)['score'];
        
        if ($last > $first) {
            return 'improving';
        } elseif ($last < $first) {
            return 'declining';
        }
        
        return 'stable';
    }
    
    public function recordScore(User $user): SafetyScore
    {
        $score = $this->calculateScore($user);
        $level = $this->getLevel($score);
        $factors = $this->getFactors($user);
        
        return SafetyScore::create([
            'user_id' => $user->id,
            'score' => $score,
            'level' => $level,
            'factors' => $factors,
            'calculated_at' => Carbon::now()
        ]);
    }
    
    public function runSafetyCheck(User $user): array
    {
        $detections = $this->detectionEngine->runDetection($user);
        $score = $this->calculateScore($user);
        $level = $this->getLevel($score);
        
        $this->recordScore($user);
        
        return [
            'detections' => $detections,
            'score' => $score,
            'level' => $level,
            'timestamp' => Carbon::now()->toIso8601String()
        ];
    }
    
    public function runSafetyCheckWithAutomation(User $user): array
    {
        $detections = $this->detectionEngine->runDetection($user);
        $score = $this->calculateScore($user);
        $level = $this->getLevel($score);
        
        $this->recordScore($user);
        
        $automationResults = [];
        if (!empty($detections)) {
            $automationResults = $this->automationEngine->processDetections($user, $detections);
        }
        
        return [
            'detections' => $detections,
            'score' => $score,
            'level' => $level,
            'timestamp' => Carbon::now()->toIso8601String(),
            'automation' => $automationResults
        ];
    }
    
    public function getDetectionEngine(): DetectionEngine
    {
        return $this->detectionEngine;
    }
    
    public function getAutomationEngine(): AutomationEngine
    {
        return $this->automationEngine;
    }
    
    public function getSafetyStatus(): array
    {
        $users = \App\Models\User::all();
        $emergencies = [];
        $totalScore = 0;
        $emergencyCount = 0;
        $atRiskCount = 0;
        
        foreach ($users as $user) {
            $score = $this->calculateScore($user);
            $level = $this->getLevel($score);
            $totalScore += $score;
            
            if ($level === 'emergency') {
                $emergencyCount++;
                $emergencies[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'score' => $score
                ];
            } elseif ($level === 'at_risk') {
                $atRiskCount++;
            }
        }
        
        $avgScore = $users->count() > 0 ? round($totalScore / $users->count()) : 0;
        
        return [
            'level' => $this->getLevel($avgScore),
            'score' => $avgScore,
            'emergencies' => $emergencies,
            'emergency_count' => $emergencyCount,
            'at_risk_count' => $atRiskCount,
            'total_users' => $users->count()
        ];
    }
    
    protected function getEventImpact(SafetyEvent $event): int
    {
        return match ($event->event_type) {
            'missed_checkin' => $this->weights['missed_checkin'] ?? 20,
            'missed_checkin_long' => $this->weights['missed_checkin_long'] ?? 30,
            'sos_triggered' => $this->weights['sos_triggered'] ?? 40,
            'sos_unacknowledged' => $this->weights['sos_unacknowledged'] ?? 40,
            'low_battery' => $this->weights['low_battery'] ?? 10,
            'low_battery_emergency' => $this->weights['low_battery_emergency'] ?? 25,
            'offline_long' => $this->weights['offline_long'] ?? 15,
            'offline_too_long' => $this->weights['offline_too_long'] ?? 15,
            'guardian_unresponsive' => $this->weights['guardian_unresponsive'] ?? 15,
            'consecutive_missed_checkins' => $this->weights['consecutive_missed_checkins'] ?? 30,
            default => 5
        };
    }
}
