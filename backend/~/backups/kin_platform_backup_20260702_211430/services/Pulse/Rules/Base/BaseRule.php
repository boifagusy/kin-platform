<?php

namespace App\Services\Pulse\Rules\Base;

use App\Models\User;
use App\Models\SafetyEvent;
use Carbon\Carbon;
use App\Services\Pulse\Rules\Contracts\DetectionRule;

abstract class BaseRule implements DetectionRule
{
    protected int $threshold = 0;
    protected string $severity = 'medium';
    
    public function getSeverity(): string
    {
        return $this->severity;
    }
    
    public function getImpact(): int
    {
        return match($this->severity) {
            'low' => 10,
            'medium' => 20,
            'high' => 30,
            'critical' => 40,
            default => 20
        };
    }
    
    protected function createEvent(User $user, array $metadata = []): ?SafetyEvent
    {
        $existing = SafetyEvent::where('user_id', $user->id)
            ->where('event_type', $this->getEventType())
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->first();
            
        if ($existing) {
            $existing->metadata = json_encode(array_merge(
                json_decode($existing->metadata, true) ?? [],
                $metadata
            ));
            $existing->save();
            return $existing;
        }
        
        return SafetyEvent::create([
            'user_id' => $user->id,
            'event_type' => $this->getEventType(),
            'metadata' => json_encode(array_merge([
                'rule' => get_class($this),
                'severity' => $this->getSeverity(),
                'detected_at' => Carbon::now()->toIso8601String()
            ], $metadata))
        ]);
    }
    
    abstract public function detect(User $user): bool;
    abstract public function getEventType(): string;
    abstract public function getDescription(): string;
}
