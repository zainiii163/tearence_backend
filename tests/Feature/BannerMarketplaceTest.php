<?php

namespace Tests\Feature;

use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Models\User;
use App\Models\AdPricingPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BannerMarketplaceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed required data
        $this->seed(\Database\Seeders\BannerMarketplaceSeeder::class);
    }

    /**
     * Test banner marketplace homepage endpoint.
     */
    public function test_banner_marketplace_homepage(): void
    {
        $response = $this->getJson('/api/v1/banner-marketplace/homepage');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'featured_banners',
                         'recent_banners',
                         'categories'
                     ]
                 ]);
    }

    /**
     * Test banner carousel endpoint.
     */
    public function test_banner_carousel(): void
    {
        $response = $this->getJson('/api/v1/banner-marketplace/carousel');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data'
                 ]);
    }

    /**
     * Test banner categories endpoint.
     */
    public function test_banner_categories(): void
    {
        $response = $this->getJson('/api/v1/banner-marketplace/categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'slug',
                             'description',
                             'color',
                             'active_banner_ads_count'
                         ]
                     ]
                 ]);
    }

    /**
     * Test banner analytics endpoint.
     */
    public function test_banner_analytics(): void
    {
        $response = $this->getJson('/api/v1/banner-marketplace/analytics');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'total_banners',
                         'total_views',
                         'total_clicks',
                         'trending_categories'
                     ]
                 ]);
    }

    /**
     * Test banner ads index endpoint.
     */
    public function test_banner_ads_index(): void
    {
        $response = $this->getJson('/api/v1/banner-ads');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);
    }

    /**
     * Test banner ads featured endpoint.
     */
    public function test_banner_ads_featured(): void
    {
        $response = $this->getJson('/api/v1/banner-ads/featured');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);
    }

    /**
     * Test banner ads most viewed endpoint.
     */
    public function test_banner_ads_most_viewed(): void
    {
        $response = $this->getJson('/api/v1/banner-ads/most-viewed');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);
    }

    /**
     * Test banner ads recent endpoint.
     */
    public function test_banner_ads_recent(): void
    {
        $response = $this->getJson('/api/v1/banner-ads/recent');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);
    }

    /**
     * Test banner ads promotion options endpoint.
     */
    public function test_banner_ads_promotion_options(): void
    {
        $response = $this->getJson('/api/v1/banner-ads/promotion-options');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'tier',
                             'name',
                             'price',
                             'currency',
                             'duration',
                             'benefits'
                         ]
                     ]
                 ]);
    }

    /**
     * Test banner categories index endpoint.
     */
    public function test_banner_categories_index(): void
    {
        $response = $this->getJson('/api/v1/banner-categories');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);
    }

    /**
     * Test banner categories trending endpoint.
     */
    public function test_banner_categories_trending(): void
    {
        $response = $this->getJson('/api/v1/banner-categories/trending');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);
    }

    /**
     * Test creating a banner ad with authentication.
     */
    public function test_create_banner_ad_authenticated(): void
    {
        $user = User::factory()->create();
        $category = BannerCategory::first();
        $plan = AdPricingPlan::where('ad_type', 'banner')->first();

        $bannerData = [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph,
            'business_name' => $this->faker->company,
            'contact_person' => $this->faker->name,
            'email' => $this->faker->companyEmail,
            'phone' => $this->faker->phoneNumber,
            'website_url' => $this->faker->url,
            'banner_type' => 'image',
            'banner_size' => '728x90',
            'banner_image' => 'test-banner.jpg',
            'destination_link' => $this->faker->url,
            'call_to_action' => 'Shop Now',
            'banner_category_id' => $category->id,
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'promotion_tier' => 'standard',
            'promotion_price' => 25.00,
            'status' => 'draft',
            'is_active' => true,
        ];

        $response = $this->actingAs($user, 'api')
                         ->postJson('/api/v1/banner-ads', $bannerData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'title',
                         'business_name',
                         'slug',
                         'status'
                     ]
                 ]);

        $this->assertDatabaseHas('banner_ads', [
            'title' => $bannerData['title'],
            'business_name' => $bannerData['business_name'],
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test updating a banner ad.
     */
    public function test_update_banner_ad(): void
    {
        $user = User::factory()->create();
        $bannerAd = BannerAd::factory()->create(['user_id' => $user->id]);
        
        $updateData = [
            'title' => 'Updated Banner Title',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($user, 'api')
                         ->putJson("/api/v1/banner-ads/{$bannerAd->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data'
                 ]);

        $this->assertDatabaseHas('banner_ads', [
            'id' => $bannerAd->id,
            'title' => 'Updated Banner Title',
        ]);
    }

    /**
     * Test deleting a banner ad.
     */
    public function test_delete_banner_ad(): void
    {
        $user = User::factory()->create();
        $bannerAd = BannerAd::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
                         ->deleteJson("/api/v1/banner-ads/{$bannerAd->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message'
                 ]);

        $this->assertSoftDeleted('banner_ads', [
            'id' => $bannerAd->id,
        ]);
    }

    /**
     * Test tracking banner click.
     */
    public function test_track_banner_click(): void
    {
        $bannerAd = BannerAd::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'clicks_count' => 0,
        ]);

        $response = $this->postJson("/api/v1/banner-ads/{$bannerAd->slug}/track-click");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'destination_link'
                 ]);

        $bannerAd->refresh();
        $this->assertEquals(1, $bannerAd->clicks_count);
    }

    /**
     * Test showing a banner ad increments view count.
     */
    public function test_show_banner_ad_increments_views(): void
    {
        $bannerAd = BannerAd::factory()->create([
            'status' => 'active',
            'is_active' => true,
            'views_count' => 0,
        ]);

        $response = $this->getJson("/api/v1/banner-ads/{$bannerAd->slug}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data'
                 ]);

        $bannerAd->refresh();
        $this->assertEquals(1, $bannerAd->views_count);
    }

    /**
     * Test my banners endpoint for authenticated user.
     */
    public function test_my_banners_endpoint(): void
    {
        $user = User::factory()->create();
        BannerAd::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')
                         ->getJson('/api/v1/banner-ads/my-banners');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'links',
                     'meta'
                 ]);

        $this->assertEquals(3, $response->json('meta.total'));
    }

    /**
     * Test unauthorized access to protected endpoints.
     */
    public function test_unauthorized_access(): void
    {
        $bannerAd = BannerAd::factory()->create();

        // Test creating without authentication
        $response = $this->postJson('/api/v1/banner-ads', []);
        $response->assertStatus(401);

        // Test updating without authentication
        $response = $this->putJson("/api/v1/banner-ads/{$bannerAd->id}", []);
        $response->assertStatus(401);

        // Test deleting without authentication
        $response = $this->deleteJson("/api/v1/banner-ads/{$bannerAd->id}");
        $response->assertStatus(401);

        // Test my banners without authentication
        $response = $this->getJson('/api/v1/banner-ads/my-banners');
        $response->assertStatus(401);
    }
}
