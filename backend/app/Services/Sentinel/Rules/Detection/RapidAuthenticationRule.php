<?php

namespace App\Services\Sentinel\Rules\Detection;

use App\Services\Sentinel\Rules\BaseRule;

class RapidAuthenticationRule extends BaseRule
{
    protected function getDefaultRuleId(): string
    {
        return 'SENTINEL-RULE-005';
    }

    protected function getDefaultName(): string
    {
        return 'Rapid Authentication Detection';
    }

    protected function getDefaultDescription(): string
    {
        return 'Detects multiple authentication attempts within a short time window';
    }

    protected function getDefaultCategory(): string
    {
        return 'authentication';
    }

    protected function getDefaultSeverity(): string
    {
        return 'medium';
    }

    protected function getDefaultRiskPoints(): int
    {
        return 10;
    }

    protected function getDefaultThreshold(): int
    {
        return 6;
    }

    protected function getDefaultTimeWindow(): int
    {
        return 60; // 1 minute
    }

    protected function getDefaultAutomatedActions(): array
    {
        return ['log'];
    }

    protected function getDefaultCooldown(): int
    {
        return 300; // 5 minutes
    }

    public function detect(array $event): bool
    {
        $ip = $event['ip'] ?? $event['source_ip'] ?? null;

        if (!$ip) {
            return false;
        }

        $count = $this->getEventsInWindowByIp('login_attempt', $ip, $this->timeWindow);
        if ($count >= $this->threshold) {
            $this->setCooldown('ip_' . $ip);
            return true;
        }

        $count = $this->getEventsInWindowByIp('login_pin_failed', $ip, $this->timeWindow);
        if ($count >= $this->threshold) {
            $this->setCooldown('ip_' . $ip);
            return true;
        }

        return false;
    }
}
