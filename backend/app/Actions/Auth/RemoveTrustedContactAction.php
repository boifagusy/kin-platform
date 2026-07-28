<?php

namespace App\Actions\Auth;

use App\Models\TrustedContact;
use App\Models\User;
use App\Models\IncidentNotification;
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

            // Resolve notifications for the recipient
            $recipient = User::where('phone', $contact->phone)->first();
            if ($recipient) {
                IncidentNotification::where('user_id', $recipient->id)
                    ->where('category', 'trusted_contact')
                    ->where('metadata->contact_id', $contact->id)
                    ->whereNull('resolved_at')
                    ->update(['resolved_at' => now()]);
            }

            Log::info('RemoveTrustedContactAction: Contact removed', [
                'user_id' => $userId,
                'contact_id' => $contactId,
            ]);

            event(new TrustedContactRemoved($contact));

            return [
                'success' => true,
                'message' => 'Contact removed',
                'data' => $contact->fresh(),
            ];
        } catch (\Exception $e) {
            Log::error('RemoveTrustedContactAction: Failed', [
                'user_id' => $userId,
                'contact_id' => $contactId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Failed to remove contact'];
        }
    }
}
