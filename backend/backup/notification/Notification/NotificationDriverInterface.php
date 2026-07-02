<?php

namespace App\Services\Notification;

interface NotificationDriverInterface
{
    public function send(string $to, string $subject, string $body, array $options = []): bool;
}
