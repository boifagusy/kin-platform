<?php

namespace App\Notifications\Drivers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsDriver implements NotificationDriverInterface
{
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->apiKey = config('services.termii.api_key');
        $this->senderId = config('services.termii.sender_id', 'KIN Alert');
    }

    public function sendSms(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('[SMS] Termii API key not configured — set TERMII_API_KEY in .env', ['phone' => $phone]);
            return false;
        }

        try {
            $response = Http::post('https://api.termii.com/api/sms/send', [
                'api_key' => $this->apiKey,
                'to' => $phone,
                'from' => $this->senderId,
                'sms' => $message,
                'type' => 'plain',
                'channel' => 'generic'
            ]);

            if (!$response->successful()) {
                Log::error('[SMS] Termii send failed', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('SMS send failed: ' . $e->getMessage());
            return false;
        }
    }

    public function sendWhatsApp(string $phone, string $message): bool
    {
        return false; // not this driver's job
    }

    public function sendEmail(string $email, string $subject, string $message): bool
    {
        return false; // not this driver's job
    }

    public function sendPush(string $deviceToken, string $title, string $body): bool
    {
        return false; // not this driver's job
    }
}
