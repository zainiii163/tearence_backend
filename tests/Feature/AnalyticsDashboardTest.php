<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAnalytics;
use App\Models\DashboardPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AnalyticsDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->adminUser = User::factory()->create([
            'email' => 'admin@test.com',
            'is_super_admin' => true,
        ]);
        
        // Create regular user
        $this->regularUser = User::factory()->create([
            'email' => 'user@test.com',
        ]);
    }

    /**
     * Test user analytics dashboard endpoint.
     */
    public function test_user_analytics_dashboard()
    {
        $token = JWTAuth::fromUser($this->regularUser);
        
        // Create some test analytics data
        UserAnalytics::create([
            'user_id' => $this->regularUser->user_id,
            'event_type' => 'login',
            'event_date' => now(),
        ]);
        
        UserAnalytics::create([
            'user_id' => $this->regularUser->user_id,
            'event_type' => 'profile_view',
            'event_date' => now(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user-analytics/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'activity_summary',
                        'daily_activity',
                        'listing_performance',
                        'recent_activity',
                        'period',
                    ],
                ]);

        $this->assertEquals(2, $response->json('data.activity_summary.login'));
        $this->assertEquals(1, $response->json('data.activity_summary.profile_view'));
    }

    /**
     * Test admin analytics dashboard with permissions.
     */
    public function test_admin_analytics_dashboard()
    {
        $token = JWTAuth::fromUser($this->adminUser);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/admin-analytics/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'permissions' => [
                        'accessible_sections',
                        'can_export',
                    ],
                    'period',
                ]);
    }

    /**
     * Test regular user cannot access admin analytics.
     */
    public function test_regular_user_cannot_access_admin_analytics()
    {
        $token = JWTAuth::fromUser($this->regularUser);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/admin-analytics/dashboard');

        $response->assertStatus(403)
                ->assertJson([
                    'error' => 'Insufficient permissions',
                ]);
    }

    /**
     * Test dashboard permission system.
     */
    public function test_dashboard_permission_system()
    {
        // Test permission checking
        $this->assertTrue(
            DashboardPermission::userCanView($this->adminUser->user_id, 'system_overview')
        );
        
        $this->assertFalse(
            DashboardPermission::userCanView($this->regularUser->user_id, 'system_overview')
        );
    }

    /**
     * Test user analytics export.
     */
    public function test_user_analytics_export()
    {
        $token = JWTAuth::fromUser($this->regularUser);
        
        UserAnalytics::create([
            'user_id' => $this->regularUser->user_id,
            'event_type' => 'login',
            'event_date' => now(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/v1/user-analytics/export?type=activity&days=7');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'export_info' => [
                        'format',
                        'type',
                        'period',
                        'exported_at',
                    ],
                ]);
    }

    /**
     * Test recording user activity.
     */
    public function test_record_user_activity()
    {
        $activity = UserAnalytics::create([
            'user_id' => $this->regularUser->user_id,
            'event_type' => 'login',
            'ip_address' => '127.0.0.1',
            'source' => 'web',
            'event_date' => now(),
        ]);

        $this->assertDatabaseHas('user_analytics', [
            'analytics_id' => $activity->analytics_id,
            'user_id' => $this->regularUser->user_id,
            'event_type' => 'login',
        ]);
    }
}
