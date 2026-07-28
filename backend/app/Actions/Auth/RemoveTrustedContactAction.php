<?php

namespace App\Actions\Auth;

use App\Models\TrustedContact;
use App\Events\TrustedContact\TrustedContactRemoved;
use Illuminate\Support\Facades\Log;

class RemoveTrustedContactAction
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

        try {
            $contact->update(['status' => 'removed']);

            Log::info('RemoveTrustedContactAction: Contact removed', [
                'user_id' => $userId,
                'contact_id' => $contactId,
            ]);

            // Dispatch event for notification
            event(new TrustedContactRemoved($contact, $userId));

            return ['success' => true, 'message' => 'Contact removed'];
        } catch (\Exception $e) {
            Log::error('RemoveTrustedContactAction: Failed', [
                'user_id' => $userId,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to remove contact: ' . $e->getMessage()];
        }
    }
}
