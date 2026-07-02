<?php

namespace App\Services\Sentinel\Rules\Detection;

use App\Services\Sentinel\Rules\BaseRule;
use App\Models\SecurityEvent;

class FailedLoginRule extends BaseRule
{
    protected function getDefaultRuleId(): string
    {
        return 'SENTINEL-RULE-001';
    }

    protected function getDefaultName(): string
    {
        return 'Failed Login Threshold';
    }

    protected function getDefaultDescription(): string
    {
        return 'Detects when a user has multiple failed login attempts within a time window';
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
        return 300; // 5 minutes
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
        $phone = $event['phone'] ?? null;
        $ip = $event['ip'] ?? $event['source_ip'] ?? null;

        if (!$phone && !$ip) {
            return false;
        }

        // Count failed logins for this phone
        if ($phone) {
            $count = $this->getEventsInWindow('login_pin_failed', $phone, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('phone_' . $phone);
                return true;
            }
        }

        // Count failed logins for this IP
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
