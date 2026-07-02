<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreatePinAction
{
    public function execute(string $phone, string $pin): array
    {
        // Validate phone number
        if (!preg_match('/^\+234\d{10}$/', $phone)) {
            return [
                'success' => false,
                'error' => 'Invalid phone number'
            ];
        }

        // Validate PIN format
        if (!preg_match('/^\d{4}$/', $pin)) {
            return [
                'success' => false,
                'error' => 'PIN must be 4 digits'
            ];
        }

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'Kin User',
                'email' => 'user_' . time() . '@kin.local'
            ]
        );

        // Hash and save the PIN
        $hashedPin = Hash::make($pin);
        $user->login_pin_hash = $hashedPin;
        $user->save();

        // Verify it was saved
        if (!$user->login_pin_hash) {
            return [
                'success' => false,
                'error' => 'Failed to save PIN'
            ];
        }

        // Create Sanctum token for API access
        $token = $user->createToken('mobile-auth')->plainTextToken;

        return [
            'success' => true,
            'message' => 'PIN created successfully',
            'user_id' => $user->id,
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
