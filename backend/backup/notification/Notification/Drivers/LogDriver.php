<?php

namespace App\Services\Notification\Drivers;

use App\Services\Notification\NotificationDriverInterface;
use Illuminate\Support\Facades\Log;

class LogDriver implements NotificationDriverInterface
{
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        Log::info('NOTIFICATION', [
            'to' => $to,
            'subject' => $subject,
            'body' => $body,
            'options' => $options
        ]);
        return true;
    }
}
