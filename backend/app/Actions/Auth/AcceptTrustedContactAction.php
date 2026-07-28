<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\TrustedContact;
use App\Events\TrustedContact\TrustedContactRequestAccepted;
use Illuminate\Support\Facades\Log;

class AcceptTrustedContactAction
{
    public function execute(int $userId, int $contactId): array
    {
        $contact = TrustedContact::find($contactId);

        if (!$contact) {
            return ['success' => false, 'error' => 'Contact not found'];
        }

        if ($contact->user_id !== $userId) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        if ($contact->status !== 'pending_request') {
            return ['success' => false, 'error' => 'Contact is not pending'];
        }

        try {
            $contact->update([
                'status' => 'accepted',
                'verified_at' => now(),
            ]);

            Log::info('AcceptTrustedContactAction: Contact accepted', [
                'user_id' => $userId,
                'contact_id' => $contactId,
            ]);

            // Dispatch event for notification
            event(new TrustedContactRequestAccepted($contact, $userId));

            return [
                'success' => true,
                'message' => 'Contact accepted',
                'data' => $contact->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('AcceptTrustedContactAction: Failed', [
                'user_id' => $userId,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to accept contact: ' . $e->getMessage()];
        }
    }
}
