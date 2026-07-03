<?php

namespace App\Services\Pulse;

use App\Models\User;
use App\Models\SafetyEvent;
use App\Services\Pulse\Rules\Contracts\DetectionRule;
use Carbon\Carbon;

class DetectionEngine
{
    protected array $rules = [];
    protected array $processedEvents = [];
    
    public function __construct()
    {
        $this->registerDefaultRules();
    }
    
    protected function registerDefaultRules(): void
    {
        $this->rules = [
            new \App\Services\Pulse\Rules\Detection\MissedCheckinRule(),
            new \App\Services\Pulse\Rules\Detection\ConsecutiveMissedCheckinsRule(),
            new \App\Services\Pulse\Rules\Detection\SosUnacknowledgedRule(),
            new \App\Services\Pulse\Rules\Detection\LowBatteryEmergencyRule(),
            new \App\Services\Pulse\Rules\Detection\OfflineTooLongRule(),
        ];
    }
    
    public function runDetection(User $user): array
    {
        $detectedEvents = [];
        $this->processedEvents = [];
        
        // Run each rule once
        foreach ($this->rules as $rule) {
            try {
                if ($rule->detect($user)) {
                    $eventType = $rule->getEventType();
                    
                    // Skip if already detected in this run
                    if (in_array($eventType, $this->processedEvents)) {
                        continue;
                    }
                    
                    $this->processedEvents[] = $eventType;
                    
                    $detectedEvents[] = [
                        'rule' => get_class($rule),
                        'type' => $eventType,
                        'severity' => $rule->getSeverity(),
                        'impact' => $rule->getImpact(),
                        'description' => $rule->getDescription()
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Detection rule failed: ' . $e->getMessage(), [
                    'rule' => get_class($rule),
                    'user_id' => $user->id
                ]);
            }
        }
        
        return $detectedEvents;
    }
    
    public function runDetectionForAllUsers(): array
    {
        $allDetections = [];
        $users = User::all();
        
        foreach ($users as $user) {
            $detections = $this->runDetection($user);
            if (!empty($detections)) {
                $allDetections[$user->id] = [
                    'user' => $user->name,
                    'email' => $user->email,
                    'detections' => $detections
                ];
            }
        }
        
        return $allDetections;
    }
    
    public function getActiveRules(): array
    {
        return array_map(function ($rule) {
            return [
                'class' => get_class($rule),
                'type' => $rule->getEventType(),
                'severity' => $rule->getSeverity(),
                'impact' => $rule->getImpact(),
                'description' => $rule->getDescription()
            ];
        }, $this->rules);
    }
    
    public function addRule(DetectionRule $rule): void
    {
        $this->rules[] = $rule;
    }
}
