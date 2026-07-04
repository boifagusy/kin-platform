<?php

namespace App\Services\Sentinel\Rules\Detection;

use App\Services\Sentinel\Rules\BaseRule;

class PasswordResetAbuseRule extends BaseRule
{
    protected function getDefaultRuleId(): string
    {
        return 'SENTINEL-RULE-004';
    }

    protected function getDefaultName(): string
    {
        return 'Password Reset Abuse Detection';
    }

    protected function getDefaultDescription(): string
    {
        return 'Detects multiple password reset attempts within a time window';
    }

    protected function getDefaultCategory(): string
    {
        return 'authentication';
    }

    protected function getDefaultSeverity(): string
    {
        return 'high';
    }

    protected function getDefaultRiskPoints(): int
    {
        return 15;
    }

    protected function getDefaultThreshold(): int
    {
        return 5;
    }

    protected function getDefaultTimeWindow(): int
    {
        return 600; // 10 minutes
    }

    protected function getDefaultAutomatedActions(): array
    {
        return ['log', 'notify_admin'];
    }

    protected function getDefaultCooldown(): int
    {
        return 600; // 10 minutes
    }

    public function detect(array $event): bool
    {
        $identifier = $event['identifier'] ?? $event['phone'] ?? null;
        $ip = $event['ip'] ?? $event['source_ip'] ?? null;

        if (!$identifier && !$ip) {
            return false;
        }

        if ($identifier) {
            $count = $this->getEventsInWindow('pin_reset_attempt', $identifier, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('identifier_' . $identifier);
                return true;
            }
        }

        if ($ip) {
            $count = $this->getEventsInWindowByIp('pin_reset_attempt', $ip, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('ip_' . $ip);
                return true;
            }
        }

        return false;
    }
}
