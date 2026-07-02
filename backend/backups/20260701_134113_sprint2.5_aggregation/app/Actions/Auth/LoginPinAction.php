<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginPinAction
{
    /**
     * WHY:
     * Verify a user's 4-digit login PIN and return API token.
     */
    public function execute(
        string $phone,
        string $pin
    ): array {

        if (
            !preg_match('/^\+234\d{10}$/', $phone)
        ) {
            return [
                'success' => false,
                'error' => 'Invalid phone number',
            ];
        }

        if (
            !preg_match('/^\d{4}$/', $pin)
        ) {
            return [
                'success' => false,
                'error' => 'PIN must be 4 digits',
            ];
        }

        $user = User::where(
            'phone',
            $phone
        )->first();

        if (!$user) {
            return [
                'success' => false,
                'error' => 'User not found',
            ];
        }

        if (
            empty($user->login_pin_hash)
        ) {
            return [
                'success' => false,
                'error' => 'PIN not set',
            ];
        }

        if (
            !Hash::check(
                $pin,
                $user->login_pin_hash
            )
        ) {
            return [
                'success' => false,
                'error' => 'Invalid PIN',
            ];
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        // Create Sanctum token for API access
        $token = $user->createToken('mobile-auth')->plainTextToken;

        return [
            'success' => true,
            'user_id' => $user->id,
            'onboarding_completed' => $user->onboarding_completed,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
