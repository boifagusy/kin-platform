<?php

namespace App\Listeners;

use App\Models\IncidentNotification;
use App\Events\TrustedContact\TrustedContactRequestCreated;
use App\Events\TrustedContact\TrustedContactRequestAccepted;
use App\Events\TrustedContact\TrustedContactRequestDeclined;
use App\Events\TrustedContact\TrustedContactInvitationAccepted;
use App\Events\TrustedContact\TrustedContactRemoved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class TrustedContactNotificationListener implements ShouldQueue
{
    /**
     * Handle TrustedContactRequestCreated → Notify recipient
     */
    public function handleTrustedContactRequestCreated(TrustedContactRequestCreated $event): void
    {
        $contact = $event->contact;

        // Check for duplicate pending notification
        $existing = IncidentNotification::where('user_id', $contact->user_id)
            ->where('category', 'trusted_contact')
            ->where('type', 'request_pending')
            ->where('metadata->contact_id', $contact->id)
            ->whereNull('resolved_at')
            ->first();

        if ($existing) {
            Log::info('Duplicate request notification blocked', ['contact_id' => $contact->id]);
            return;
        }

        IncidentNotification::create([
            'user_id' => $contact->user_id,
            'category' => 'trusted_contact',
            'type' => 'request_pending',
            'title' => $contact->name . ' wants to add you',
            'message' => $contact->name . ' wants to add you as a Trusted Contact.',
            'metadata' => [
                'contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'contact_phone' => $contact->phone,
                'initiator_id' => $event->initiatorUserId,
            ],
        ]);

        Log::info('TrustedContactNotificationListener: Request notification created', [
            'contact_id' => $contact->id,
            'recipient_id' => $contact->user_id,
        ]);
    }

    /**
     * Handle TrustedContactRequestAccepted → Resolve notifications
     */
    public function handleTrustedContactRequestAccepted(TrustedContactRequestAccepted $event): void
    {
        $contact = $event->contact;

        IncidentNotification::where('metadata->contact_id', $contact->id)
            ->where('category', 'trusted_contact')
            ->whereNull('resolved_at')
            ->update(['resolved_at' => now()]);

        Log::info('TrustedContactNotificationListener: Request accepted, notifications resolved', [
            'contact_id' => $contact->id,
        ]);
    }

    /**
     * Handle TrustedContactRequestDeclined → Resolve notifications
     */
    public function handleTrustedContactRequestDeclined(TrustedContactRequestDeclined $event): void
    {
        $contact = $event->contact;

        IncidentNotification::where('metadata->contact_id', $contact->id)
            ->where('category', 'trusted_contact')
            ->whereNull('resolved_at')
            ->update(['resolved_at' => now()]);

        Log::info('TrustedContactNotificationListener: Request declined, notifications resolved', [
            'contact_id' => $contact->id,
        ]);
    }

    /**
     * Handle TrustedContactInvitationAccepted → Resolve notifications
     */
    public function handleTrustedContactInvitationAccepted(TrustedContactInvitationAccepted $event): void
    {
        $contact = $event->contact;

        IncidentNotification::where('metadata->contact_id', $contact->id)
            ->where('category', 'trusted_contact')
            ->whereNull('resolved_at')
            ->update(['resolved_at' => now()]);

        Log::info('TrustedContactNotificationListener: Invitation accepted, notifications resolved', [
            'contact_id' => $contact->id,
        ]);
    }

    /**
     * Handle TrustedContactRemoved → Resolve all related notifications
     */
    public function handleTrustedContactRemoved(TrustedContactRemoved $event): void
    {
        $contact = $event->contact;

        IncidentNotification::where('metadata->contact_id', $contact->id)
            ->where('category', 'trusted_contact')
            ->whereNull('resolved_at')
            ->update(['resolved_at' => now()]);

        Log::info('TrustedContactNotificationListener: Contact removed, notifications resolved', [
            'contact_id' => $contact->id,
        ]);
    }
}
