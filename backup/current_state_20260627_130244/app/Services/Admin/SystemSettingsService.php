<?php

namespace App\Services\Admin;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingsService
{
    protected $cacheKey = 'system_settings';
    protected $cacheTtl = 3600; // 1 hour

    /**
     * Get all settings as key-value array
     */
    public function getAllSettings(): array
    {
        return Cache::remember($this->cacheKey, $this->cacheTtl, function () {
            $settings = SystemSetting::all();
            $result = [];
            foreach ($settings as $setting) {
                $value = $this->castValue($setting->value, $setting->type);
                $result[$setting->key] = $value;
            }

            return $result;
        });
    }

    /**
     * Get a specific setting by key
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->getAllSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Update multiple settings at once
     */
    public function updateSettings(array $settings): bool
    {
        try {
            foreach ($settings as $key => $value) {
                $setting = SystemSetting::where('key', $key)->first();
                if ($setting) {
                    $setting->value = $this->prepareValue($value, $setting->type);
                    $setting->save();
                }
            }

            // Clear cache after updates
            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cast value based on type
     */
    protected function castValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            default:
                return (string) $value;
        }
    }

    /**
     * Prepare value for storage
     */
    protected function prepareValue($value, string $type)
    {
        if ($type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
        }
        return (string) $value;
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    /**
     * Get default settings for initialization
     */
    public function getDefaultSettings(): array
    {
        return [
            // Email SMTP Configuration
            'mail_mailer' => 'log',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@kin.com',
            'mail_from_name' => 'KIN Safety',

            // OTP Delivery Channels
            'otp_sms_enabled' => true,
            'otp_email_enabled' => true,
            'otp_whatsapp_enabled' => false,

            // OTP Configuration
            'otp_code_length' => 6,
            'otp_expiry_minutes' => 10,
            'otp_resend_cooldown' => 60,

            // Rate Limiting
            'rate_limit_enabled' => true,
            'rate_limit_attempts' => 5,
            'rate_limit_lockout_minutes' => 30,

            // Data Retention
            'retention_otp_days' => 90,
            'retention_audit_days' => 365,
        ];
    }

    /**
     * Initialize settings if they don't exist
     */
    public function initializeSettings(): void
    {
        $defaults = $this->getDefaultSettings();

        foreach ($defaults as $key => $defaultValue) {
            $exists = SystemSetting::where('key', $key)->exists();

            if (!$exists) {
                $type = is_bool($defaultValue) ? 'boolean' : (is_int($defaultValue) ? 'integer' : 'string');
                $value = is_bool($defaultValue) ? ($defaultValue ? '1' : '0') : (string) $defaultValue;

                SystemSetting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => $type,
                    'description' => $this->getDescriptionForSetting($key),
                    'group_name' => $this->getGroupForSetting($key),
                    'is_public' => false,
                ]);
            }
        }

        $this->clearCache();
    }

    protected function getDescriptionForSetting(string $key): string
    {
        $descriptions = [
            'mail_mailer' => 'Mail driver (smtp, sendmail, log)',
            'mail_host' => 'SMTP server hostname',
            'mail_port' => 'SMTP server port',
            'mail_username' => 'SMTP username',
            'mail_password' => 'SMTP password',
            'mail_encryption' => 'Encryption type (tls, ssl)',
            'mail_from_address' => 'From email address',
            'mail_from_name' => 'From name for emails',
            'otp_sms_enabled' => 'Enable SMS delivery for OTP codes',
            'otp_email_enabled' => 'Enable Email delivery for OTP codes',
            'otp_whatsapp_enabled' => 'Enable WhatsApp delivery for OTP codes',
            'otp_code_length' => 'Number of digits in OTP code',
            'otp_expiry_minutes' => 'Time before OTP expires',
            'otp_resend_cooldown' => 'Minimum time before user can request another OTP',
            'rate_limit_enabled' => 'Enable rate limiting for OTP requests',
            'rate_limit_attempts' => 'Maximum attempts per hour before lockout',
            'rate_limit_lockout_minutes' => 'Duration of lockout after exceeding attempts',
            'retention_otp_days' => 'Days to keep OTP logs before deletion',
            'retention_audit_days' => 'Days to keep audit logs before deletion',
        ];

        return $descriptions[$key] ?? '';
    }

    protected function getGroupForSetting(string $key): string
    {
        if (str_starts_with($key, 'mail_')) return 'email';
        if (str_starts_with($key, 'otp_')) return 'otp';
        if (str_starts_with($key, 'rate_limit_')) return 'security';
        if (str_starts_with($key, 'retention_')) return 'retention';
        return 'general';
    }
}
