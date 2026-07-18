<?php
namespace App\Services;

use App\Notifications\Drivers\NotificationDriverInterface;
use App\Notifications\Drivers\SmsDriver;
use App\Notifications\Drivers\EmailDriver;
use App\Notifications\Drivers\WhatsAppDriver;
use App\Notifications\Drivers\LogDriver;

class NotificationDriverManager
{
    private array $drivers = [];

    public function __construct()
    {
        $this->register('sms', new SmsDriver());
        $this->register('email', new EmailDriver());
        $this->register('whatsapp', new WhatsAppDriver());
        $this->register('push', new LogDriver()); // Push via FCM deferred
        $this->register('log', new LogDriver());
    }

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

    public function send(string $channel, array $recipient, array $message): bool
    {
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

    public function health(): array
    {
        $status = [];
        foreach ($this->drivers as $channel => $driver) {
            $status[$channel] = [
                'driver' => get_class($driver),
                'configured' => true,
            ];
        }
        return $status;
    }

    public function channels(): array
    {
        return array_keys($this->drivers);
    }
}
