<?php

namespace App\Actions\Auth;

use App\Models\TrustedContact;
use App\Services\TokenSecurityService;
use App\Events\TrustedContact\TrustedContactInvitationAccepted;
use Illuminate\Support\Facades\Log;

class VerifyInvitationAction
{
    public function execute(string $rawToken): array
    {
        $tokenHash = hash('sha256', $rawToken);

        $contact = TrustedContact::where('token_hash', $tokenHash)
            ->where('status', 'pending_invitation')
            ->first();

        if (!$contact) {
            Log::warning('VerifyInvitationAction: Contact not found for token');
            return ['success' => false, 'error' => 'Invalid or expired token'];
        }

        [$isValid, $error] = TokenSecurityService::verifyToken(
            $rawToken,
            $contact->token_hash,
            $contact->token_expires_at
        );

        if (!$isValid) {
            if ($contact->token_expires_at && now()->isAfter($contact->token_expires_at)) {
                $contact->update(['status' => 'expired']);
                Log::info('VerifyInvitationAction: Invitation marked as expired', ['contact_id' => $contact->id]);
            }
            return ['success' => false, 'error' => $error];
        }

        try {
            $contact->update([
                'status' => 'accepted',
                'verified_at' => now(),
                'token_hash' => null,
                'token_expires_at' => null,
            ]);

            TokenSecurityService::invalidateToken();

            Log::info('VerifyInvitationAction: Invitation verified and accepted', [
                'contact_id' => $contact->id,
                'user_id' => $contact->user_id,
            ]);

            // Dispatch event for notification
            event(new TrustedContactInvitationAccepted($contact, $contact->phone));

            return [
                'success' => true,
                'message' => 'Invitation verified and contact accepted',
                'data' => $contact->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('VerifyInvitationAction: Failed to verify', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to verify invitation: ' . $e->getMessage()];
        }
    }
}
