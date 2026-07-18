<?php

namespace App\Services;

use App\Models\UserPreference;

class NotificationDriverManager
{
    private array $drivers = [];

    public function register(string $channel, NotificationDriverInterface $driver): void
    {
        $this->drivers[$channel] = $driver;
    }

    public function driver(string $channel): NotificationDriverInterface
    {
        if (!isset($this->drivers[$channel])) {
            throw new \InvalidArgumentException("Unknown channel: {$channel}");
        }
        return $this->drivers[$channel];
    }

    public function send(string $channel, array $recipient, array $message, ?int $userId = null): bool
    {
        if ($userId !== null && !$this->isChannelEnabled($userId, $channel)) {
            return false;
        }

        $driver = $this->driver($channel);

        return match ($channel) {
            'sms' => $driver->sendSms($recipient['phone'], $message['body']),
            'email' => $driver->sendEmail($recipient['email'], $message['subject'] ?? '', $message['body']),
            'whatsapp' => $driver->sendWhatsApp($recipient['phone'], $message['body']),
            'push' => $driver->sendPush($recipient['device_token'] ?? '', $message['title'] ?? '', $message['body']),
            'log' => $driver->sendSms($recipient['phone'] ?? 'log', $message['body']),
            default => throw new \InvalidArgumentException("Send not implemented for: {$channel}"),
        };
    }

    public function isChannelEnabled(int $userId, string $channel): bool
    {
        $preferences = UserPreference::getPreferences($userId);
        return $preferences['channels'][$channel] ?? true;
    }

    public function isCategoryEnabled(int $userId, string $category): bool
    {
        $preferences = UserPreference::getPreferences($userId);
        return $preferences['categories'][$category] ?? true;
    }

    public function health(): array
    {
        $status = [];
        foreach ($this->drivers as $channel => $driver) {
            $status[$channel] = [
                'driver' => get_class($driver),
                'healthy' => $driver->healthCheck(),
            ];
        }
        return $status;
    }

    public function channels(): array
    {
        return array_keys($this->drivers);
    }
}
