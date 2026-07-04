<?php

namespace App\Services\Sentinel\Rules\Detection;

use App\Services\Sentinel\Rules\BaseRule;

class OtpSpamRule extends BaseRule
{
    protected function getDefaultRuleId(): string
    {
        return 'SENTINEL-RULE-003';
    }

    protected function getDefaultName(): string
    {
        return 'OTP Spam Detection';
    }

    protected function getDefaultDescription(): string
    {
        return 'Detects excessive OTP requests within a time window';
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
        return 3;
    }

    protected function getDefaultTimeWindow(): int
    {
        return 120; // 2 minutes
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
        $identifier = $event['phone'] ?? $event['identifier'] ?? null;
        $ip = $event['ip'] ?? $event['source_ip'] ?? null;

        if (!$identifier && !$ip) {
            return false;
        }

        if ($identifier) {
            $count = $this->getEventsInWindow('otp_requested', $identifier, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('identifier_' . $identifier);
                return true;
            }
        }

        if ($ip) {
            $count = $this->getEventsInWindowByIp('otp_requested', $ip, $this->timeWindow);
            if ($count >= $this->threshold) {
                $this->setCooldown('ip_' . $ip);
                return true;
            }
        }

        return false;
    }
}
