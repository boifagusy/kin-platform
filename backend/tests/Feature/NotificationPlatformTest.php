<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\IncidentNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationPlatformTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    /** @test */
    public function it_creates_incident_notification()
    {
        $user = User::factory()->create();

        $notification = $this->service->createIncident([
            'user_id' => $user->id,
            'type' => 'incident',
            'title' => 'Test Notification',
            'body' => 'Test body',
            'category' => 'security',
        ]);

        $this->assertNotNull($notification);
        $this->assertEquals('Test Notification', $notification->title);
        $this->assertDatabaseHas('incident_notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_notifications_with_idempotency_key()
    {
        $user = User::factory()->create();
        $key = 'test-key-' . uniqid();

        $first = $this->service->createIncident([
            'user_id' => $user->id,
            'type' => 'incident',
            'title' => 'First',
            'body' => 'Body',
            'category' => 'security',
            'idempotency_key' => $key,
        ]);

        $second = $this->service->createIncident([
            'user_id' => $user->id,
            'type' => 'incident',
            'title' => 'Duplicate',
            'body' => 'Body',
            'category' => 'security',
            'idempotency_key' => $key,
        ]);

        $this->assertEquals($first->id, $second->id);
    }

    /** @test */
    public function it_gets_unread_count()
    {
        $user = User::factory()->create();

        $this->service->createIncident([
            'user_id' => $user->id,
            'type' => 'incident',
            'title' => 'Unread',
            'body' => 'Body',
            'category' => 'security',
        ]);

        $count = $this->service->getUnreadCount($user->id);
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function it_marks_all_read()
    {
        $user = User::factory()->create();

        $this->service->createIncident([
            'user_id' => $user->id,
            'type' => 'incident',
            'title' => 'To Read',
            'body' => 'Body',
            'category' => 'security',
        ]);

        $this->service->markAllRead($user->id);
        $count = $this->service->getUnreadCount($user->id);
        $this->assertEquals(0, $count);
    }
}
