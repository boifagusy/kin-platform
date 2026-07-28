<?php

namespace App\Listeners;

use App\Events\TrustedContact\TrustedContactRequestCreated;
use App\Events\TrustedContact\TrustedContactRequestAccepted;
use App\Events\TrustedContact\TrustedContactRequestDeclined;
use App\Events\TrustedContact\TrustedContactInvitationAccepted;
use App\Events\TrustedContact\TrustedContactRemoved;
use App\Models\IncidentNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TrustedContactNotificationListener
{
    /**
     * Handle TrustedContactRequestCreated
     * Recipient (the person being added) gets notified
     */
    public function handleTrustedContactRequestCreated(TrustedContactRequestCreated $event): void
    {
        $recipient = User::where('phone', $event->contact->phone)->first();
        if (!$recipient) {
            Log::warning('TrustedContactNotificationListener: Recipient not found', ['phone' => $event->contact->phone]);
            return;
        }

        $this->checkDuplicateAndPersist(
            $recipient->id,
            'trusted_contact',
            'request_pending',
            $event->contact->name . ' wants to add you as a Trusted Contact.',
            $event->contact->name . ' wants to add you',
            [
                'contact_id' => $event->contact->id,
                'contact_name' => $event->contact->name,
                'contact_phone' => $event->contact->phone,
                'initiator_id' => $event->initiatorUserId,
            ]
        );
    }

    /**
     * Handle TrustedContactRequestAccepted
     * Initiator gets notified
     */
    public function handleTrustedContactRequestAccepted(TrustedContactRequestAccepted $event): void
    {
        $this->persistNotification(
            $event->contact->user_id,
            'trusted_contact',
            'request_accepted',
            $event->contact->name . ' accepted your request',
            [
                'contact_id' => $event->contact->id,
                'contact_name' => $event->contact->name,
            ]
        );
    }

    /**
     * Handle TrustedContactRequestDeclined
     * Initiator gets notified
     */
    public function handleTrustedContactRequestDeclined(TrustedContactRequestDeclined $event): void
    {
        $this->persistNotification(
            $event->contact->user_id,
            'trusted_contact',
            'request_declined',
            $event->contact->name . ' declined your request',
            [
                'contact_id' => $event->contact->id,
                'contact_name' => $event->contact->name,
            ]
        );
    }

    /**
     * Handle TrustedContactInvitationAccepted
     * Initiator gets notified
     */
    public function handleTrustedContactInvitationAccepted(TrustedContactInvitationAccepted $event): void
    {
        $this->persistNotification(
            $event->contact->user_id,
            'trusted_contact',
            'invitation_accepted',
            $event->contact->name . ' accepted your invitation',
            [
                'contact_id' => $event->contact->id,
                'contact_name' => $event->contact->name,
            ]
        );
    }

    /**
     * Handle TrustedContactRemoved
     * Recipient gets notified
     */
    public function handleTrustedContactRemoved(TrustedContactRemoved $event): void
    {
        $recipient = User::where('phone', $event->contact->phone)->first();
        if (!$recipient) {
            Log::warning('TrustedContactNotificationListener: Recipient not found for removal', ['phone' => $event->contact->phone]);
            return;
        }

        $this->persistNotification(
            $recipient->id,
            'trusted_contact',
            'removed',
            $event->contact->name . ' removed you from their Trusted Contacts',
            [
                'contact_id' => $event->contact->id,
                'contact_name' => $event->contact->name,
            ]
        );
    }

    /**
     * Check for duplicate and persist notification
     */
    private function checkDuplicateAndPersist(int $userId, string $category, string $type, string $message, string $title, array $metadata): void
    {
        $existing = IncidentNotification::where('user_id', $userId)
            ->where('category', $category)
            ->where('type', $type)
            ->where('metadata->contact_id', $metadata['contact_id'])
            ->whereNull('resolved_at')
            ->first();

        if ($existing) {
            Log::info('TrustedContactNotificationListener: Duplicate notification blocked', ['contact_id' => $metadata['contact_id']]);
            return;
        }

        $this->persistNotification($userId, $category, $type, $title, $metadata, $message);
    }

    /**
     * Persist notification
     */
    private function persistNotification(int $userId, string $category, string $type, string $title, array $metadata, ?string $message = null): void
    {
        try {
            IncidentNotification::create([
                'user_id' => $userId,
                'category' => $category,
                'type' => $type,
                'title' => $title,
                'message' => $message ?? $title,
                'metadata' => $metadata,
            ]);

            Log::info('TrustedContactNotificationListener: Notification created', [
                'user_id' => $userId,
                'category' => $category,
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            Log::error('TrustedContactNotificationListener: Failed to persist', [
                'user_id' => $userId,
                'category' => $category,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
