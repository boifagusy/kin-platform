<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\TrustedContact;
use Illuminate\Support\Facades\Log;

class SaveTrustedContactAction
{
    /**
     * Save a trusted contact for a user.
     *
     * @param string $userPhone The phone number of the user adding the contact
     * @param string $contactName The display name of the trusted contact
     * @param string $contactPhone The phone number of the trusted contact
     * @param bool $inviteSent Whether an invite was sent
     * @return array
     */
    public function execute(
        string $userPhone,
        string $contactName,
        string $contactPhone,
        bool $inviteSent = false
    ): array {
        // Find the user by phone
        $user = User::where('phone', $userPhone)->first();

        if (!$user) {
            Log::warning('SaveTrustedContactAction: User not found', ['phone' => $userPhone]);
            return [
                'success' => false,
                'error' => 'User not found',
            ];
        }

        // Check if this trusted contact already exists
        $existing = TrustedContact::where('user_id', $user->id)
            ->where('phone', $contactPhone)
            ->first();

        if ($existing) {
            Log::info('SaveTrustedContactAction: Contact already exists', [
                'user_id' => $user->id,
                'contact_phone' => $contactPhone
            ]);
            return [
                'success' => true,
                'message' => 'Trusted contact already exists',
                'data' => $existing
            ];
        }

        // Create the trusted contact
        try {
            $trustedContact = TrustedContact::create([
                'user_id' => $user->id,
                'name' => $contactName,
                'phone' => $contactPhone,
                'verified' => $inviteSent ? false : true, // If invite sent, not verified yet
                'active' => true,
            ]);

            Log::info('SaveTrustedContactAction: Trusted contact saved', [
                'user_id' => $user->id,
                'contact_id' => $trustedContact->id,
                'contact_phone' => $contactPhone
            ]);

            return [
                'success' => true,
                'message' => 'Trusted contact saved successfully',
                'data' => $trustedContact
            ];

        } catch (\Exception $e) {
            Log::error('SaveTrustedContactAction: Failed to save', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to save trusted contact: ' . $e->getMessage(),
            ];
        }
    }
}
