<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'phone' => '+2348086448522',
            'name' => 'Test User',
        ]);

        $this->token = $this->user->createToken('test')->plainTextToken;
    }

    public function test_can_get_all_preferences(): void
    {
        $response = $this->getJson('/api/v1/user/preferences', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'safety',
                    'notifications',
                    'privacy',
                    'appearance',
                    'account',
                ],
            ]);
    }

    public function test_can_get_safety_preferences(): void
    {
        $response = $this->getJson('/api/v1/user/preferences/category/safety', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'monitoring',
                    'location_tracking',
                    'checkin_interval',
                    'sos_power_button',
                ],
            ]);
    }

    public function test_can_update_preferences(): void
    {
        $response = $this->patchJson('/api/v1/user/preferences', [
            'category' => 'safety',
            'preferences' => [
                'monitoring' => false,
                'location_tracking' => false,
                'checkin_interval' => 120,
            ],
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify the update
        $verify = $this->getJson('/api/v1/user/preferences/category/safety', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $verify->assertJson([
            'success' => true,
            'data' => [
                'monitoring' => false,
                'location_tracking' => false,
                'checkin_interval' => 120,
            ],
        ]);
    }

    public function test_can_reset_preference(): void
    {
        // First, set a preference
        $this->patchJson('/api/v1/user/preferences', [
            'category' => 'safety',
            'preferences' => ['monitoring' => false],
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Reset it
        $response = $this->deleteJson('/api/v1/user/preferences/safety/monitoring', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify it's reset to default (true)
        $verify = $this->getJson('/api/v1/user/preferences/category/safety', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $verify->assertJson([
            'success' => true,
            'data' => ['monitoring' => true],
        ]);
    }

    public function test_cannot_update_without_auth(): void
    {
        $response = $this->patchJson('/api/v1/user/preferences', [
            'category' => 'safety',
            'preferences' => ['monitoring' => false],
        ]);

        $response->assertStatus(401);
    }

    public function test_cannot_get_without_auth(): void
    {
        $response = $this->getJson('/api/v1/user/preferences');
        $response->assertStatus(401);
    }

    public function test_invalid_category_returns_error(): void
    {
        $response = $this->patchJson('/api/v1/user/preferences', [
            'category' => 'invalid_category',
            'preferences' => ['monitoring' => false],
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422);
    }

    public function test_preferences_persist_in_database(): void
    {
        // Set preferences
        $this->patchJson('/api/v1/user/preferences', [
            'category' => 'safety',
            'preferences' => [
                'monitoring' => false,
                'checkin_interval' => 90,
            ],
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        // Query database directly
        $pref = UserPreference::where('user_id', $this->user->id)
            ->where('category', 'safety')
            ->where('preference_key', 'monitoring')
            ->first();

        $this->assertNotNull($pref);
        $this->assertEquals('boolean', $pref->value_type);
        $this->assertEquals('0', $pref->value);

        $pref2 = UserPreference::where('user_id', $this->user->id)
            ->where('category', 'safety')
            ->where('preference_key', 'checkin_interval')
            ->first();

        $this->assertNotNull($pref2);
        $this->assertEquals('integer', $pref2->value_type);
        $this->assertEquals('90', $pref2->value);
    }
}
