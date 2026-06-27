<?php

namespace App\Notifications\Drivers;

use App\Notifications\Drivers\NotificationDriverInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailDriver implements NotificationDriverInterface
{
    public function sendSms(string $phone, string $message): bool
    {
        Log::warning('[EmailDriver] SMS not supported', ['phone' => $phone]);
        return false;
    }

    public function sendWhatsApp(string $phone, string $message): bool
    {
        Log::warning('[EmailDriver] WhatsApp not supported', ['phone' => $phone]);
        return false;
    }

    public function sendEmail(string $email, string $subject, string $message): bool
    {
        try {
            Mail::raw($message, function ($m) use ($email, $subject) {
                $m->to($email)->subject($subject);
            });
            Log::info('[EmailDriver] Email sent', ['to' => $email, 'subject' => $subject]);
            return true;
        } catch (\Exception $e) {
            Log::error('[EmailDriver] Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendPush(string $deviceToken, string $title, string $body): bool
    {
        Log::warning('[EmailDriver] Push not supported', ['device_token' => $deviceToken]);
        return false;
    }
}
