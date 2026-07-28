<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TrustedContact;
use App\Actions\Auth\SaveTrustedContactAction;

class SaveTrustedContactActionTest extends TestCase
{
    protected SaveTrustedContactAction $action;
    protected User $user;
    protected User $registeredContactUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new SaveTrustedContactAction();
        $this->user = User::factory()->create(['phone' => '+234 801 1122233']);
        $this->registeredContactUser = User::factory()->create(['phone' => '+234 802 3334444']);
    }

    public function test_reject_empty_name(): void
    {
        $result = $this->action->execute($this->user->id, '', '+234 803 5556666');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('name must be', $result['error']);
    }

    public function test_reject_invalid_phone(): void
    {
        $result = $this->action->execute($this->user->id, 'John Doe', '123');
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('10 digits', $result['error']);
    }

    public function test_reject_exceeding_free_limit(): void
    {
        $result1 = $this->action->execute($this->user->id, 'Contact 1', '+234 803 5556666');
        $this->assertTrue($result1['success']);
        
        $result2 = $this->action->execute($this->user->id, 'Contact 2', '+234 804 7778888');
        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('limited to 1', $result2['error']);
    }

    public function test_reject_self_add(): void
    {
        $result = $this->action->execute($this->user->id, 'Myself', $this->user->phone);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('own phone', $result['error']);
    }

    public function test_return_existing_contact(): void
    {
        $result1 = $this->action->execute($this->user->id, 'John Doe', '+234 803 5556666');
        $this->assertTrue($result1['success']);
        $contactId = $result1['data']->id;
        
        $result2 = $this->action->execute($this->user->id, 'John Doe', '+234 803 5556666');
        $this->assertTrue($result2['success']);
        $this->assertTrue($result2['existing']);
        $this->assertEquals($contactId, $result2['data']->id);
    }

    public function test_create_pending_request_for_registered_user(): void
    {
        $result = $this->action->execute($this->user->id, 'Registered Friend', $this->registeredContactUser->phone);
        $this->assertTrue($result['success']);
        $this->assertEquals('pending_request', $result['status']);
        $this->assertFalse($result['verification_required']);
    }

    public function test_create_pending_invitation_for_unregistered_user(): void
    {
        $result = $this->action->execute($this->user->id, 'Unregistered Friend', '+234 809 9990000');
        $this->assertTrue($result['success']);
        $this->assertEquals('pending_invitation', $result['status']);
        $this->assertTrue($result['verification_required']);
        $this->assertNotNull($result['verification_token']);
    }

    public function test_token_security(): void
    {
        $result = $this->action->execute($this->user->id, 'Test Contact', '+234 810 1110000');
        $this->assertTrue($result['success']);
        
        $rawToken = $result['verification_token'];
        $this->assertNotNull($rawToken);
        
        $contact = TrustedContact::find($result['data']->id);
        $storedHash = $contact->token_hash;
        $this->assertNotNull($storedHash);
        $this->assertNotEquals($rawToken, $storedHash);
        $this->assertEquals($storedHash, hash('sha256', $rawToken));
    }

    public function test_status_pending_request(): void
    {
        $result = $this->action->execute($this->user->id, 'Registered Contact', $this->registeredContactUser->phone);
        $this->assertTrue($result['success']);
        $contact = TrustedContact::find($result['data']->id);
        $this->assertEquals('pending_request', $contact->status);
    }

    public function test_status_pending_invitation(): void
    {
        $result = $this->action->execute($this->user->id, 'Unregistered Contact', '+234 811 2220000');
        $this->assertTrue($result['success']);
        $contact = TrustedContact::find($result['data']->id);
        $this->assertEquals('pending_invitation', $contact->status);
    }

    public function test_verified_derives_from_status(): void
    {
        $contact = TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'pending_request']);
        $this->assertFalse($contact->verified);
        
        $contact->update(['status' => 'accepted']);
        $this->assertTrue($contact->fresh()->verified);
        
        $contact->update(['status' => 'declined']);
        $this->assertFalse($contact->fresh()->verified);
    }

    public function test_verified_scope(): void
    {
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'accepted']);
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'pending_request']);
        
        $verified = TrustedContact::verified()->forUser($this->user)->get();
        $this->assertCount(1, $verified);
        $this->assertEquals('accepted', $verified->first()->status);
    }

    public function test_pending_scope(): void
    {
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'pending_request']);
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'pending_invitation']);
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'accepted']);
        
        $pending = TrustedContact::pending()->forUser($this->user)->get();
        $this->assertCount(2, $pending);
    }

    public function test_active_scope(): void
    {
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'pending_request']);
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'accepted']);
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'removed']);
        TrustedContact::factory()->create(['user_id' => $this->user->id, 'status' => 'declined']);
        
        $active = TrustedContact::active()->forUser($this->user)->get();
        $this->assertCount(2, $active);
    }
}
