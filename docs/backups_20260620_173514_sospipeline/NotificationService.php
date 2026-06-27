<?php

namespace App\Services;

use App\Notifications\Drivers\LogDriver;
use App\Notifications\Drivers\SmsDriver;
use App\Notifications\Drivers\EmailDriver;
use App\Notifications\Drivers\WhatsAppDriver;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $driver;
    protected $smsDriver;
    protected $whatsAppDriver;
    protected $settings;

    public function __construct()
    {
        $this->loadSettings();
        $this->driver = $this->resolveDriver();
        // SMS, WhatsApp, and Email are independent channels — each has its
        // own dedicated driver instance, gated by its own _enabled flag.
        // notification_driver / resolveDriver() only controls the fallback
        // LogDriver used elsewhere; it's not a mutually-exclusive mode switch.
        $this->smsDriver = new SmsDriver();
        $this->whatsAppDriver = new WhatsAppDriver();
    }

    protected function loadSettings(): void
    {
        $this->settings = SystemSetting::where('group_name', 'notifications')
            ->orWhere('group_name', 'otp')
            ->get()
            ->keyBy('key')
            ->map(fn($s) => $this->castValue($s->value, $s->type));
    }

    protected function castValue($value, string $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            default => (string) $value,
        };
    }

    protected function resolveDriver()
    {
        $driver = $this->settings['notification_driver'] ?? 'log';

        return match ($driver) {
            'email' => new EmailDriver(),
            'sms' => new SmsDriver(),
            default => new LogDriver(),
        };
    }

    public function isEnabled(string $channel): bool
    {
        return $this->settings["{$channel}_enabled"] ?? false;
    }

    public function sendSms(string $phone, string $message): bool
    {
        if (!($this->settings['otp_sms_enabled'] ?? false)) {
            Log::info('[SMS] Disabled by admin settings', ['phone' => $phone]);
            return false;
        }

        return $this->smsDriver->sendSms($phone, $message);
    }

    public function sendWhatsApp(string $phone, string $message): bool
    {
        if (!($this->settings['otp_whatsapp_enabled'] ?? false)) {
            Log::info('[WhatsApp] Disabled by admin settings', ['phone' => $phone]);
            return false;
        }

        return $this->whatsAppDriver->sendWhatsApp($phone, $message);
    }

    public function sendEmail(string $email, string $subject, string $message): bool
    {
        if (!($this->settings['otp_email_enabled'] ?? false)) {
            Log::info('[Email] Disabled by admin settings', ['email' => $email]);
            return false;
        }

        // Email always goes through EmailDriver directly too, for the same
        // independence reason as SMS/WhatsApp above.
        return (new EmailDriver())->sendEmail($email, $subject, $message);
    }

    /**
     * Sends an OTP across every enabled channel for this phone.
     */
    public function sendOtp(string $phone, string $otp): bool
    {
        $message = "Your KIN verification code is: {$otp}";
        $sentAny = false;

        if (($this->settings['otp_sms_enabled'] ?? false)) {
            $sentAny = $this->sendSms($phone, $message) || $sentAny;
        }

        if (($this->settings['otp_whatsapp_enabled'] ?? false)) {
            $sentAny = $this->sendWhatsApp($phone, $message) || $sentAny;
        }

        return $sentAny;
    }

    public function getDriverName(): string
    {
        return $this->settings['notification_driver'] ?? 'log';
    }

    public function getSettings(): array
    {
        return $this->settings->toArray();
    }
}
