<?php

namespace App\Notifications\Drivers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppDriver implements NotificationDriverInterface
{
    protected ?string $baseUrl;
    protected ?string $apiKey;
    protected ?string $sessionId;

    public function __construct()
    {
        $this->baseUrl = SystemSetting::getValue('otp_whatsapp_api_url');
        $this->apiKey = SystemSetting::getValue('otp_whatsapp_api_key');
        $this->sessionId = SystemSetting::getValue('otp_whatsapp_session_id');
    }

    public function sendWhatsApp(string $phone, string $message): bool
    {
        if (empty($this->baseUrl) || empty($this->apiKey) || empty($this->sessionId)) {
            Log::warning('[WhatsApp] OpenWA not configured — set otp_whatsapp_api_url/api_key/session_id in admin settings', [
                'phone' => $phone,
            ]);
            return false;
        }

        $url = rtrim($this->baseUrl, '/') . "/api/sessions/{$this->sessionId}/messages/send-text";

        try {
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->timeout(15)
                ->post($url, [
                    'chatId' => $phone . '@c.us',
                    'text' => $message,
                ]);

            if ($response->failed()) {
                Log::error('[WhatsApp] OpenWA send failed', [
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('[WhatsApp] OpenWA send exception: ' . $e->getMessage(), ['phone' => $phone]);
            return false;
        }
    }

    public function sendSms(string $phone, string $message): bool
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
