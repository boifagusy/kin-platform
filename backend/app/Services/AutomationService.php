<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AutomationService
{
    public function __construct(
        private RateLimiter $rateLimiter,
        private QuietHoursResolver $quietHours,
        private ChannelFallbackResolver $fallbackResolver,
        private NotificationDriverManager $driverManager,
    ) {}

    public function evaluate(string $eventType, array $payload): void
    {
        $rules = $this->resolveRules($eventType);

        foreach ($rules as $rule) {
            try {
                $this->executeRule($rule, $payload);
            } catch (\Exception $e) {
                Log::error('[N9] Automation rule execution failed', [
                    'event' => $eventType,
                    'rule' => $rule['name'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function resolveRules(string $eventType): array
    {
        return config("automation.rules.{$eventType}", []);
    }

    private function executeRule(array $rule, array $payload): void
    {
        $userId = $payload['user_id'] ?? null;
        $category = $rule['category'] ?? 'general';

        // Check conditions
        if (!$this->evaluateConditions($rule['conditions'] ?? [], $payload)) {
            Log::info('[N9] Rule conditions not met', ['rule' => $rule['name'] ?? 'unknown']);
            return;
        }

        // Check quiet hours
        if ($this->quietHours->shouldDefer($category)) {
            Log::info('[N9] Rule deferred — quiet hours', ['rule' => $rule['name'] ?? 'unknown', 'category' => $category]);
            return;
        }

        // Check rate limit
        $channel = $rule['channel'] ?? 'push';
        if ($userId && $this->rateLimiter->isLimited($userId, $channel)) {
            Log::warning('[N9] Rate limited', ['user_id' => $userId, 'channel' => $channel]);
            return;
        }

        // Resolve channel with fallback
        if ($userId) {
            $resolvedChannel = $this->fallbackResolver->resolve($channel, $userId);
            if (!$resolvedChannel) {
                Log::warning('[N9] No available channel after fallback', ['user_id' => $userId]);
                return;
            }
            $channel = $resolvedChannel;
        }

        // Dispatch notification via existing pipeline
        $message = $this->buildMessage($rule, $payload);
        $recipient = $this->buildRecipient($payload, $channel);

        $this->driverManager->send($channel, $recipient, $message, $userId);

        // Increment rate limit
        if ($userId) {
            $this->rateLimiter->increment($userId, $channel);
        }

        Log::info('[N9] Automation rule executed', [
            'rule' => $rule['name'] ?? 'unknown',
            'event' => $payload['event_type'] ?? 'unknown',
            'user_id' => $userId,
            'channel' => $channel,
        ]);
    }

    private function evaluateConditions(array $conditions, array $payload): bool
    {
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? null;
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? null;

            $actualValue = $payload[$field] ?? null;

            $result = match ($operator) {
                '=' => $actualValue == $value,
                '!=' => $actualValue != $value,
                '>' => $actualValue > $value,
                '<' => $actualValue < $value,
                '>=' => $actualValue >= $value,
                '<=' => $actualValue <= $value,
                'in' => in_array($actualValue, (array) $value),
                'not_in' => !in_array($actualValue, (array) $value),
                default => true,
            };

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    private function buildMessage(array $rule, array $payload): array
    {
        $title = $this->interpolate($rule['title_template'] ?? 'Notification', $payload);
        $body = $this->interpolate($rule['body_template'] ?? '', $payload);

        return [
            'title' => $title,
            'body' => $body,
            'subject' => $title,
        ];
    }

    private function buildRecipient(array $payload, string $channel): array
    {
        return [
            'phone' => $payload['phone'] ?? null,
            'email' => $payload['email'] ?? null,
            'device_token' => $payload['device_token'] ?? null,
        ];
    }

    private function interpolate(string $template, array $payload): string
    {
        foreach ($payload as $key => $value) {
            if (is_scalar($value)) {
                $template = str_replace("{{$key}}", (string) $value, $template);
            }
        }
        return $template;
    }
}
