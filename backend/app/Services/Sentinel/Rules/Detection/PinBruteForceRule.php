<?php

namespace App\Services\Sentinel\Rules\Detection;

use App\Services\Sentinel\Rules\BaseRule;

class PinBruteForceRule extends BaseRule
{
    protected function getDefaultRuleId(): string
    {
        return 'SENTINEL-RULE-002';
    }

    protected function getDefaultName(): string
    {
        return 'PIN Brute Force Detection';
    }

    protected function getDefaultDescription(): string
    {
        return 'Detects multiple PIN verification failures within a time window';
    }

    protected function getDefaultCategory(): string
    {
        return 'authentication';
    }

    protected function getDefaultSeverity(): string
    {
        return 'critical';
    }

    protected function getDefaultRiskPoints(): int
    {
        return 20;
    }

    protected function getDefaultThreshold(): int
    {
        return 3;
    }

    protected function getDefaultTimeWindow(): int
    {
        return 180; // 3 minutes
    }

    protected function getDefaultAutomatedActions(): array
    {
        return ['log', 'notify_admin', 'lock_account'];
    }

    protected function getDefaultCooldown(): int
    {
        return 900; // 15 minutes
    }

    public function detect(array $event): bool
    {
        $userId = $event['user_id'] ?? null;
        $ip = $event['ip'] ?? $event['source_ip'] ?? null;

        if (!$userId && !$ip) {
            return false;
        }

        if ($userId) {
            $count = $this->getEventsInWindowByUserId('login_pin_failed', $userId, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('user_' . $userId);
                return true;
            }
        }

        if ($ip) {
            $count = $this->getEventsInWindowByIp('login_pin_failed', $ip, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('ip_' . $ip);
                return true;
            }
        }

        return false;
    }
}
