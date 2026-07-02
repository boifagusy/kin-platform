<?php

namespace App\Services\Sentinel;

use App\Models\SecurityEvent;
use App\Services\Sentinel\Rules\Contracts\DetectionRule;
use App\Services\Sentinel\Rules\Detection\FailedLoginRule;
use App\Services\Sentinel\Rules\Detection\PinBruteForceRule;
use App\Services\Sentinel\Rules\Detection\OtpSpamRule;
use App\Services\Sentinel\Rules\Detection\PasswordResetAbuseRule;
use App\Services\Sentinel\Rules\Detection\RapidAuthenticationRule;
use Illuminate\Support\Facades\Log;

class DetectionEngine
{
    protected array $rules = [];
    protected ResponseEngine $responseEngine;
    protected RiskEngine $riskEngine;

    public function __construct()
    {
        $this->responseEngine = new ResponseEngine();
        $this->riskEngine = new RiskEngine();
        $this->registerDefaultRules();
    }

    public function registerRule(DetectionRule $rule): void
    {
        $this->rules[$rule->getRuleId()] = $rule;
    }

    public function registerDefaultRules(): void
    {
        $this->registerRule(new FailedLoginRule());
        $this->registerRule(new PinBruteForceRule());
        $this->registerRule(new OtpSpamRule());
        $this->registerRule(new PasswordResetAbuseRule());
        $this->registerRule(new RapidAuthenticationRule());
    }

    public function processEvent(SecurityEvent $event): array
    {
        $detections = [];
        $eventData = $this->prepareEventData($event);

        foreach ($this->rules as $rule) {
            if (!$rule->isEnabled()) {
                continue;
            }

            if ($rule->detect($eventData)) {
                $detection = [
                    'rule_id' => $rule->getRuleId(),
                    'rule_name' => $rule->getName(),
                    'severity' => $rule->getSeverity(),
                    'risk_points' => $rule->getRiskPoints(),
                    'automated_actions' => $rule->getAutomatedActions(),
                    'event_id' => $event->id,
                    'timestamp' => now()->toISOString(),
                ];

                $detections[] = $detection;

                Log::warning('Sentinel detection triggered', [
                    'rule' => $rule->getRuleId(),
                    'event' => $event->id,
                    'severity' => $rule->getSeverity(),
                ]);

                // Update risk score for user
                if ($event->user_id) {
                    $this->riskEngine->updateScore($event->user_id, $event->event_type, $eventData);
                }

                // Execute automated responses
                $this->responseEngine->execute($event, $detection);
            }
        }

        return $detections;
    }

    protected function prepareEventData(SecurityEvent $event): array
    {
        return [
            'id' => $event->id,
            'event_type' => $event->event_type,
            'severity' => $event->severity,
            'source_ip' => $event->source_ip,
            'user_id' => $event->user_id,
            'details' => $event->details ?? [],
            'phone' => $event->details['phone'] ?? $event->details['identifier'] ?? null,
            'identifier' => $event->details['identifier'] ?? $event->details['phone'] ?? null,
            'ip' => $event->source_ip,
            'created_at' => $event->created_at,
        ];
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getRule(string $ruleId): ?DetectionRule
    {
        return $this->rules[$ruleId] ?? null;
    }
}
