<?php

namespace App\Actions\Auth;

use App\Models\TrustedContact;
use App\Models\User;
use App\Models\IncidentNotification;
use App\Events\TrustedContact\TrustedContactRequestDeclined;
use Illuminate\Support\Facades\Log;

class DeclineTrustedContactAction
{
    public function execute(int $recipientUserId, int $contactId): array
    {
        $contact = TrustedContact::find($contactId);

        if (!$contact) {
            return ['success' => false, 'error' => 'Contact not found'];
        }

        $recipient = User::find($recipientUserId);
        if (!$recipient || $recipient->phone !== $contact->phone) {
            return ['success' => false, 'error' => 'Unauthorized'];
        }

        if ($contact->status !== 'pending_request') {
            return ['success' => false, 'error' => 'Contact is not pending'];
        }

        try {
            $contact->update(['status' => 'declined']);

            IncidentNotification::where('user_id', $recipientUserId)
                ->where('category', 'trusted_contact')
                ->where('metadata->contact_id', $contact->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);

            Log::info('DeclineTrustedContactAction: Contact declined', [
                'recipient_id' => $recipientUserId,
                'contact_id' => $contactId,
            ]);

            event(new TrustedContactRequestDeclined($contact, $contact->user_id));

            return [
                'success' => true,
                'message' => 'Contact declined',
                'data' => $contact->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('DeclineTrustedContactAction: Failed', [
                'recipient_id' => $recipientUserId,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to decline contact'];
        }
    }
}
