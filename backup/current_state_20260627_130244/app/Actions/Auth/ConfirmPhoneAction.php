<?php

namespace App\Actions\Auth;

use App\Models\User;

class ConfirmPhoneAction
{
    /**
     * WHY:
     * Check whether a phone number already
     * exists and determine the next step.
     */
    public function execute(
        string $phone
    ): array {
        if (
            empty($phone) ||
            !preg_match('/^\+234\d{10}$/', $phone)
        ) {
            return [
                'success' => false,
                'error' => 'Invalid phone number',
            ];
        }

        $user = User::where(
            'phone',
            $phone
        )->first();

        return [
            'success' => true,
            'exists' => $user !== null,
            'masked_phone' => $this->maskPhone($phone),
            'next_step' => $user
                ? 'login_pin'
                : 'register',
        ];
    }

    /**
     * WHY:
     * Hide middle digits.
     */
    private function maskPhone(
        string $phone
    ): string {
        return substr($phone, 0, 4)
            . '******'
            . substr($phone, -4);
    }
}

