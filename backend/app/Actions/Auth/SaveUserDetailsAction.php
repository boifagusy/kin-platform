<?php

namespace App\Actions\Auth;

use App\Models\User;

class SaveUserDetailsAction
{
    /**
     * Save user profile details.
     */
    public function execute(
        string $phone,
        string $fullName,
        ?string $email = null
    ): array {

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

        // Prepare update data
        $updateData = [
            'name' => $fullName,
        ];

        // Only update email if provided and not empty
        if ($email && trim($email) !== '') {
            $normalizedEmail = strtolower(trim($email));
            
            // Prevent duplicate email assignment
            $emailExists = User::where('email', $normalizedEmail)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return [
                    'success' => false,
                    'error' => 'This email is already in use by another account.',
                ];
            }

            $updateData['email'] = $normalizedEmail;
        }

        $user->update($updateData);

        return [
            'success' => true,
            'message' => 'User details saved',
        ];
    }
}
