<?php

namespace App\Services\Notification\Drivers;

use App\Services\Notification\NotificationDriverInterface;
use Illuminate\Support\Facades\Mail;

class EmailDriver implements NotificationDriverInterface
{
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
            return true;
        } catch (\Exception $e) {
            \Log::error('EmailDriver failed: ' . $e->getMessage());
            return false;
        }
    }
}
