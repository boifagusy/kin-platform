<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\TrustedContact;
use App\Services\TokenSecurityService;
use App\Events\TrustedContact\TrustedContactRequestCreated;
use Illuminate\Support\Facades\Log;

class SaveTrustedContactAction
{
    public function execute(
        int $userId,
        string $contactName,
        string $contactPhone,
        bool $inviteSent = false
    ): array {
        if (!$contactName || strlen(trim($contactName)) < 2) {
            return ['success' => false, 'error' => 'Contact name must be at least 2 characters.'];
        }

        if (!$contactPhone || strlen(preg_replace('/[^0-9]/', '', $contactPhone)) < 10) {
            return ['success' => false, 'error' => 'Phone number must be at least 10 digits.'];
        }

        $user = User::find($userId);
        if (!$user) {
            Log::warning('SaveTrustedContactAction: User not found', ['user_id' => $userId]);
            return ['success' => false, 'error' => 'User not found'];
        }

        $existingCount = TrustedContact::where('user_id', $user->id)
            ->whereNotIn('status', ['removed', 'declined', 'cancelled', 'expired'])
            ->count();

        if ($existingCount >= 1) {
            return ['success' => false, 'error' => 'Free tier limited to 1 trusted contact. Upgrade to add more.'];
        }

        $cleanUserPhone = preg_replace('/[^0-9]/', '', $user->phone ?? '');
        $cleanContactPhone = preg_replace('/[^0-9]/', '', $contactPhone);

        if ($cleanUserPhone && $cleanUserPhone === $cleanContactPhone) {
            return ['success' => false, 'error' => 'You cannot add your own phone number as a trusted contact.'];
        }

        $existing = TrustedContact::where('user_id', $user->id)
            ->where('phone', $contactPhone)
            ->first();

        if ($existing) {
            Log::info('SaveTrustedContactAction: Contact already exists', [
                'user_id' => $user->id,
                'contact_phone' => $contactPhone,
                'status' => $existing->status
            ]);
            return [
                'success' => true,
                'message' => 'Trusted contact already exists',
                'data' => $existing,
                'existing' => true
            ];
        }

        $registeredUser = User::where('phone', $contactPhone)->first();
        $status = $registeredUser ? 'pending_request' : 'pending_invitation';
        $requiresVerification = !$registeredUser;

        try {
            $tokenData = null;
            $verificationToken = null;

            if ($requiresVerification) {
                $tokenData = TokenSecurityService::generateInvitationToken();
                $verificationToken = $tokenData['raw_token'];
            }

            $trustedContact = TrustedContact::create([
                'user_id' => $user->id,
                'name' => trim($contactName),
                'phone' => $contactPhone,
                'status' => $status,
                'token_hash' => $tokenData['token_hash'] ?? null,
                'token_expires_at' => $tokenData['expires_at'] ?? null,
                'active' => true,
            ]);

            Log::info('SaveTrustedContactAction: Trusted contact created', [
                'user_id' => $user->id,
                'contact_id' => $trustedContact->id,
                'status' => $status,
            ]);

            // Dispatch event for notification
            if ($status === 'pending_request' && $registeredUser) {
                event(new TrustedContactRequestCreated($trustedContact, $user->id));
            }

            return [
                'success' => true,
                'message' => 'Trusted contact saved successfully',
                'data' => $trustedContact,
                'status' => $status,
                'verification_required' => $requiresVerification,
                'verification_token' => $verificationToken,
            ];

        } catch (\Exception $e) {
            Log::error('SaveTrustedContactAction: Failed to save', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'error' => 'Failed to save trusted contact: ' . $e->getMessage()];
        }
    }
}
