<?php

namespace App\Notifications\Drivers;

interface NotificationDriverInterface
{
    public function sendSms(string $phone, string $message): bool;
    public function sendWhatsApp(string $phone, string $message): bool;
    public function sendEmail(string $email, string $subject, string $message): bool;
    public function sendPush(string $deviceToken, string $title, string $body): bool;
}
