<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordReset;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;

class PasswordResetService
{
    protected NotificationService $notifier;

    public function __construct(NotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * Resolve a user from either an email or a phone identifier.
     */
    protected function resolveUser(string $identifier): ?User
    {
        $identifier = trim($identifier);

        if (str_contains($identifier, '@')) {
            return User::where('email', $identifier)->first();
        }

        return User::where('phone', $identifier)->first();
    }

    /**
     * Generate and send OTP for PIN reset, by email OR phone.
     * Enforces the admin-configured resend cooldown per phone number,
     * regardless of which IP or identifier format the request came from.
     */
    public function sendResetOtp(string $identifier): array
    {
        $user = $this->resolveUser($identifier);

        // Always return a generic success message even if no user exists,
        // so this endpoint can't be used to enumerate registered accounts.
        if (!$user || empty($user->phone)) {
            return [
                'success' => true,
                'message' => 'If an account exists, an OTP has been sent.',
            ];
        }

        $phone = $user->phone;
        $cooldownSeconds = (int) SystemSetting::getValue('otp_resend_cooldown', 60);

        $existing = PasswordReset::where('phone', $phone)->first();

        if ($existing) {
            $secondsSinceLastSend = now()->diffInSeconds($existing->updated_at);

            if ($secondsSinceLastSend < $cooldownSeconds) {
                $wait = $cooldownSeconds - $secondsSinceLastSend;

                \Log::info('PasswordResetService: cooldown active, request ignored', [
                    'phone' => $phone,
                    'seconds_remaining' => $wait,
                ]);

                // Still return a generic success-shaped response so this
                // can't be used to enumerate accounts or probe cooldown
                // state, but don't actually generate/send a new code.
                return [
                    'success' => true,
                    'message' => 'If an account exists, an OTP has been sent.',
                    'cooldown_remaining' => $wait,
                ];
            }
        }

        $length = (int) SystemSetting::getValue('otp_code_length', 6);
        $expiryMinutes = (int) SystemSetting::getValue('otp_expiry_minutes', 10);

        $max = (10 ** $length) - 1;
        $otp = str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);

        PasswordReset::updateOrCreate(
            ['phone' => $phone],
            [
                'otp' => Hash::make($otp),
                'expires_at' => now()->addMinutes($expiryMinutes),
                'used' => false,
            ]
        );

        $sent = $this->notifier->sendOtp($phone, $otp);

        if (str_contains($identifier, '@') && !str_ends_with($user->email ?? '', '@kin.local')) {
            $this->notifier->sendEmail(
                $user->email,
                'Your KIN verification code',
                "Your KIN verification code is: {$otp}"
            );
        }

        if (!$sent) {
            \Log::warning('PasswordResetService: OTP generated but no channel succeeded', ['phone' => $phone]);
        }

        return [
            'success' => true,
            'message' => 'If an account exists, an OTP has been sent.',
        ];
    }

    /**
     * Verify OTP and mark as used.
     */
    public function verifyOtp(string $identifier, string $otp): array
    {
        $user = $this->resolveUser($identifier);

        if (!$user || empty($user->phone)) {
            return [
                'success' => false,
                'message' => 'No valid OTP found. Please request a new one.',
            ];
        }

        $reset = PasswordReset::where('phone', $user->phone)
            ->where('expires_at', '>', now())
            ->where('used', false)
            ->first();

        if (!$reset) {
            return [
                'success' => false,
                'message' => 'No valid OTP found. Please request a new one.',
            ];
        }

        if (!Hash::check($otp, $reset->otp)) {
            return [
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ];
        }

        $reset->update(['used' => true]);

        return [
            'success' => true,
            'message' => 'OTP verified successfully.',
        ];
    }

    /**
     * Reset PIN using a verified OTP.
     */
    public function resetPin(string $identifier, string $otp, string $newPin): array
    {
        $user = $this->resolveUser($identifier);

        if (!$user || empty($user->phone)) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP. Please request a new one.',
            ];
        }

        $reset = PasswordReset::where('phone', $user->phone)
            ->where('expires_at', '>', now())
            ->first();

        if (!$reset || !Hash::check($otp, $reset->otp)) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP. Please request a new one.',
            ];
        }

        $user->update([
            'login_pin_hash' => Hash::make($newPin),
        ]);

        $reset->update(['used' => true]);

        return [
            'success' => true,
            'message' => 'PIN reset successfully.',
        ];
    }
}
