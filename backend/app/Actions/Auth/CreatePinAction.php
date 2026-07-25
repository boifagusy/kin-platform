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

        // WHY: the users_phone_unique index covers the raw phone column and
        // ignores deleted_at, while SoftDeletes hides trashed rows from the
        // default query. firstOrCreate() therefore saw "no user", attempted an
        // INSERT, and collided with a soft-deleted row holding the same phone.
        // Look up including trashed rows and restore rather than re-insert.
        $user = User::withTrashed()
            ->where('phone', $phone)
            ->first();

        if ($user) {
            if ($user->trashed()) {
                $user->restore();
            }
        } else {
            $user = User::create([
                'phone' => $phone,
                'name' => 'Kin User',
                'email' => 'user_' . time() . '@kin.local',
            ]);
        }

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
