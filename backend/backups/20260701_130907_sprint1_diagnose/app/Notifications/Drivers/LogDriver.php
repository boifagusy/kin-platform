<?php

namespace App\Notifications\Drivers;

use Illuminate\Support\Facades\Log;

class LogDriver implements NotificationDriverInterface
{
    public function sendSms(string $phone, string $message): bool
    {
        Log::info('[SMS] Would send to: ' . $phone, ['message' => $message]);
        return true;
    }

    public function sendWhatsApp(string $phone, string $message): bool
    {
        Log::info('[WhatsApp] Would send to: ' . $phone, ['message' => $message]);
        return true;
    }

    public function sendEmail(string $email, string $subject, string $message): bool
    {
        Log::info('[Email] Would send to: ' . $email, ['subject' => $subject, 'message' => $message]);
        return true;
    }

    public function sendPush(string $deviceToken, string $title, string $body): bool
    {
        Log::info('[Push] Would send to: ' . $deviceToken, ['title' => $title, 'body' => $body]);
        return true;
    }
}
