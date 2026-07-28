<?php

namespace App\Actions\Auth;

use App\Models\TrustedContact;
use App\Events\TrustedContact\TrustedContactRequestDeclined;
use Illuminate\Support\Facades\Log;

class DeclineTrustedContactAction
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

        if (!in_array($contact->status, ['pending_request', 'pending_invitation'])) {
            return ['success' => false, 'error' => 'Contact is not pending'];
        }

        try {
            $contact->update(['status' => 'declined']);

            Log::info('DeclineTrustedContactAction: Contact declined', [
                'user_id' => $userId,
                'contact_id' => $contactId,
            ]);

            // Dispatch event for notification
            event(new TrustedContactRequestDeclined($contact, $userId));

            return [
                'success' => true,
                'message' => 'Contact declined',
                'data' => $contact->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('DeclineTrustedContactAction: Failed', [
                'user_id' => $userId,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to decline contact: ' . $e->getMessage()];
        }
    }
}
