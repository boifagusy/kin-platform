<?php

namespace App\Services;

use App\Models\TrustedContact;
use App\Models\User;

class TrustedContactService
{
    const STATUS_PENDING_REQUEST = 'pending_request';
    const STATUS_PENDING_INVITATION = 'pending_invitation';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_DECLINED = 'declined';
    const STATUS_REMOVED = 'removed';

    const STATUS_PENDING = 'pending_request';

    /**
     * Create a trusted contact
     */
    public function create(int $userId, string $name, string $phone, ?string $invitationToken = null): TrustedContact
    {
        $existingUser = User::where('phone', $phone)->first();

        $contact = TrustedContact::create([
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'status' => $existingUser ? self::STATUS_PENDING_REQUEST : self::STATUS_PENDING_INVITATION,
            'token_hash' => $invitationToken ? hash('sha256', $invitationToken) : null,
        ]);

        return $contact;
    }

    /**
     * Accept a trusted contact request
     */
    public function accept(int $userId, int $contactId): TrustedContact
    {
        $contact = $this->findForUser($userId, $contactId);

        if (!in_array($contact->status, [self::STATUS_PENDING_REQUEST, self::STATUS_PENDING_INVITATION])) {
            throw new \Exception('Only pending contacts can be accepted.');
        }

        $contact->update(['status' => self::STATUS_ACCEPTED]);

        return $contact;
    }

    /**
     * Decline a trusted contact request
     */
    public function decline(int $userId, int $contactId): void
    {
        $contact = $this->findForUser($userId, $contactId);

        if ($contact->status !== self::STATUS_PENDING) {
            throw new \Exception('Only pending contacts can be rejected.');
        }

        $contact->delete();
    }

    /**
     * Remove a trusted contact
     */
    public function remove(int $userId, int $contactId): void
    {
        $contact = $this->findForUser($userId, $contactId);
        $contact->update(['status' => self::STATUS_REMOVED]);
    }

    /**
     * Find a contact for a user
     */
    public function findForUser(int $userId, int $contactId): TrustedContact
    {
        return TrustedContact::where('user_id', $userId)
            ->where('id', $contactId)
            ->firstOrFail();
    }

    /**
     * Get all contacts for a user
     */
    public function getForUser(int $userId)
    {
        return TrustedContact::where('user_id', $userId)->get();
    }

    /**
     * Check if a contact already exists for the user
     */
    public function isAlreadyTrustedContact(int $userId, string $contactPhone): bool
    {
        return TrustedContact::where('user_id', $userId)
            ->where('phone', $contactPhone)
            ->exists();
    }
}
