<?php

namespace App\Actions\Auth;

use App\Models\TrustedContact;
use App\Models\User;
use App\Models\IncidentNotification;
use App\Events\TrustedContact\TrustedContactRequestAccepted;
use Illuminate\Support\Facades\Log;

class AcceptTrustedContactAction
{
    public function execute(int $recipientUserId, int $contactId): array
    {
        $contact = TrustedContact::find($contactId);

        if (!$contact) {
            return ['success' => false, 'error' => 'Contact not found'];
        }

        // Verify the user accepting is the recipient (phone match)
        $recipient = User::find($recipientUserId);
        if (!$recipient || $recipient->phone !== $contact->phone) {
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

            // Resolve any open notifications for this contact
            IncidentNotification::where('user_id', $recipientUserId)
                ->where('category', 'trusted_contact')
                ->where('metadata->contact_id', $contact->id)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);

            Log::info('AcceptTrustedContactAction: Contact accepted', [
                'recipient_id' => $recipientUserId,
                'contact_id' => $contactId,
            ]);

            event(new TrustedContactRequestAccepted($contact, $contact->user_id));

            return [
                'success' => true,
                'message' => 'Contact accepted',
                'data' => $contact->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('AcceptTrustedContactAction: Failed', [
                'recipient_id' => $recipientUserId,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to accept contact'];
        }
    }
}
