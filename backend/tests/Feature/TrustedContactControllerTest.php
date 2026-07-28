<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TrustedContact;

class TrustedContactControllerTest extends TestCase
{
    protected User $user;
    protected User $anotherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['phone' => '+234 801 1122233']);
        $this->anotherUser = User::factory()->create(['phone' => '+234 802 3334444']);
    }

    /**
     * RT-001: POST /trusted-contacts — Add Contact
     */
    public function test_add_contact(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/trusted-contacts', [
            'contact_name' => 'John Doe',
            'contact_phone' => '+234 803 5556666',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('trusted_contacts', [
            'user_id' => $this->user->id,
            'name' => 'John Doe',
        ]);
    }

    /**
     * RT-002: GET /trusted-contacts — Get Current Contact
     */
    public function test_get_current_contact(): void
    {
        $contact = TrustedContact::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/trusted-contacts');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.id', $contact->id);
    }

    /**
     * RT-003: GET /trusted-contacts/pending — Pending Requests & Invitations
     */
    public function test_get_pending_contacts(): void
    {
        TrustedContact::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_request',
        ]);
        TrustedContact::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_invitation',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/trusted-contacts/pending');

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'count' => 2]);
    }

    /**
     * RT-004: POST /trusted-contacts/{id}/accept — Accept Request
     */
    public function test_accept_request(): void
    {
        $contact = TrustedContact::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_request',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/trusted-contacts/{$contact->id}/accept");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals('accepted', $contact->fresh()->status);
    }

    /**
     * RT-005: POST /trusted-contacts/{id}/decline — Decline Request
     */
    public function test_decline_request(): void
    {
        $contact = TrustedContact::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending_request',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/trusted-contacts/{$contact->id}/decline");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals('declined', $contact->fresh()->status);
    }

    /**
     * RT-006: POST /trusted-contacts/verify — Verify Invitation Token
     */
    public function test_verify_invitation_token(): void
    {
        $rawToken = 'test-token-1234567890-abcdefghijk';
        $tokenHash = hash('sha256', $rawToken);

        $contact = TrustedContact::factory()->create([
            'status' => 'pending_invitation',
            'token_hash' => $tokenHash,
            'token_expires_at' => now()->addDays(7),
        ]);

        $response = $this->postJson('/api/v1/trusted-contacts/verify', [
            'token' => $rawToken,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals('accepted', $contact->fresh()->status);
    }

    /**
     * RT-007: DELETE /trusted-contacts/{id} — Remove Contact
     */
    public function test_remove_contact(): void
    {
        $contact = TrustedContact::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/v1/trusted-contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertEquals('removed', $contact->fresh()->status);
    }

    /**
     * RT-008: Unauthorized Access — User cannot modify another's contact
     */
    public function test_unauthorized_access(): void
    {
        $contact = TrustedContact::factory()->create([
            'user_id' => $this->anotherUser->id,
            'status' => 'pending_request',
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/v1/trusted-contacts/{$contact->id}/accept");

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);
    }

    /**
     * RT-009: Invalid ID — Contact not found
     */
    public function test_invalid_contact_id(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/trusted-contacts/99999/accept');

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /**
     * RT-010: API Response Format — Consistent JSON structure
     */
    public function test_api_response_format(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/trusted-contacts', [
            'contact_name' => 'Test',
            'contact_phone' => '+234 804 7778888',
        ]);

        $response->assertJsonStructure([
            'success',
            'message',
            'data',
        ]);
    }
}
