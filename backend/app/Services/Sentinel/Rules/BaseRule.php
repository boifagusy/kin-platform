<?php

namespace App\Services\Sentinel\Rules;

use App\Services\Sentinel\Rules\Contracts\DetectionRule;
use App\Models\SecurityEvent;
use Illuminate\Support\Facades\Cache;

abstract class BaseRule implements DetectionRule
{
    protected array $config;
    protected array $metadata;
    protected string $ruleId;
    protected string $name;
    protected string $description;
    protected string $category;
    protected string $severity;
    protected int $riskPoints;
    protected int $threshold;
    protected int $timeWindow;
    protected bool $enabled;
    protected array $automatedActions;
    protected int $cooldown;
    protected string $version;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->initialize();
    }

    protected function initialize(): void
    {
        $this->ruleId = $this->config['rule_id'] ?? $this->getDefaultRuleId();
        $this->name = $this->config['name'] ?? $this->getDefaultName();
        $this->description = $this->config['description'] ?? $this->getDefaultDescription();
        $this->category = $this->config['category'] ?? $this->getDefaultCategory();
        $this->severity = $this->config['severity'] ?? $this->getDefaultSeverity();
        $this->riskPoints = $this->config['risk_points'] ?? $this->getDefaultRiskPoints();
        $this->threshold = $this->config['threshold'] ?? $this->getDefaultThreshold();
        $this->timeWindow = $this->config['time_window'] ?? $this->getDefaultTimeWindow();
        $this->enabled = $this->config['enabled'] ?? true;
        $this->automatedActions = $this->config['automated_actions'] ?? $this->getDefaultAutomatedActions();
        $this->cooldown = $this->config['cooldown'] ?? $this->getDefaultCooldown();
        $this->version = $this->config['version'] ?? '1.0.0';
    }

    abstract protected function getDefaultRuleId(): string;
    abstract protected function getDefaultName(): string;
    abstract protected function getDefaultDescription(): string;
    abstract protected function getDefaultCategory(): string;
    abstract protected function getDefaultSeverity(): string;
    abstract protected function getDefaultRiskPoints(): int;
    abstract protected function getDefaultThreshold(): int;
    abstract protected function getDefaultTimeWindow(): int;
    abstract protected function getDefaultAutomatedActions(): array;
    abstract protected function getDefaultCooldown(): int;

    public function getRuleId(): string { return $this->ruleId; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function getSeverity(): string { return $this->severity; }
    public function getRiskPoints(): int { return $this->riskPoints; }
    public function getThreshold(): int { return $this->threshold; }
    public function getTimeWindow(): int { return $this->timeWindow; }
    public function isEnabled(): bool { return $this->enabled; }
    public function getAutomatedActions(): array { return $this->automatedActions; }
    public function getCooldown(): int { return $this->cooldown; }
    public function getVersion(): string { return $this->version; }

    protected function getEventsInWindow(string $eventType, string $identifier, int $seconds): int
    {
        $start = now()->subSeconds($seconds);
        return SecurityEvent::where('event_type', $eventType)
            ->where('details->identifier', $identifier)
            ->where('created_at', '>=', $start)
            ->count();
    }

    protected function getEventsInWindowByIp(string $eventType, string $ip, int $seconds): int
    {
        $start = now()->subSeconds($seconds);
        return SecurityEvent::where('event_type', $eventType)
            ->where('source_ip', $ip)
            ->where('created_at', '>=', $start)
            ->count();
    }

    protected function getEventsInWindowByUserId(string $eventType, int $userId, int $seconds): int
    {
        $start = now()->subSeconds($seconds);
        return SecurityEvent::where('event_type', $eventType)
            ->where('user_id', $userId)
            ->where('created_at', '>=', $start)
            ->count();
    }

    protected function isInCooldown(string $key): bool
    {
        return Cache::has('sentinel_rule_cooldown_' . $this->ruleId . '_' . $key);
    }

    protected function setCooldown(string $key): void
    {
        Cache::put('sentinel_rule_cooldown_' . $this->ruleId . '_' . $key, true, $this->cooldown);
    }
}
