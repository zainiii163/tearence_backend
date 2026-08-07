<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\SponsoredCategory;

/**
 * Seeds sample sponsored adverts using the live sponsored_adverts schema.
 */
class SponsoredAdvertSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('user_id') ?? 1;
        $categoryId = SponsoredCategory::query()->value('id');

        $adverts = [
            [
                'title' => 'Luxury Villa with Ocean View',
                'tagline' => 'Panoramic ocean views in Palm Jumeirah',
                'description' => 'Stunning 5-bedroom villa with panoramic ocean views, private pool, and modern amenities. Perfect for families or those seeking luxury living.',
                'overview' => 'Premium waterfront villa',
                'key_features' => 'Ocean view, private pool, 5 bedrooms, modern amenities',
                'what_makes_special' => 'Exclusive Palm Jumeirah location',
                'why_sponsored' => 'Featured luxury property launch',
                'additional_notes' => null,
                'advert_type' => 'Property',
                'category_id' => $categoryId,
                'country' => 'United Arab Emirates',
                'city' => 'Dubai',
                'latitude' => 25.2048,
                'longitude' => 55.2708,
                'location_precision' => 'exact',
                'price' => 2500000.00,
                'currency' => 'USD',
                'condition' => 'new',
                'main_image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1200&q=80',
                'additional_images' => json_encode([
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=800&q=80',
                ]),
                'video_link' => null,
                'seller_name' => 'Premium Properties',
                'business_name' => 'Premium Properties LLC',
                'phone' => '+971501234567',
                'email' => 'contact@premium.com',
                'website' => 'https://premiumproperties.com',
                'social_links' => json_encode([]),
                'logo' => null,
                'verified_seller' => true,
                'sponsorship_tier' => 'premium',
                'sponsorship_price' => 500.00,
                'payment_status' => 'paid',
                'payment_transaction_id' => 'txn_seed_villa_001',
                'sponsorship_start_date' => Carbon::now()->subDays(2),
                'sponsorship_end_date' => Carbon::now()->addDays(28),
                'views_count' => 15420,
                'saves_count' => 210,
                'inquiries_count' => 48,
                'rating' => 4.9,
                'rating_count' => 32,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'slug' => 'luxury-villa-with-ocean-view',
                'tags' => json_encode(['villa', 'dubai', 'luxury']),
                'seo_meta' => json_encode(['title' => 'Luxury Villa with Ocean View']),
                'created_by' => $userId,
                'updated_by' => $userId,
                'user_id' => $userId,
            ],
            [
                'title' => 'Modern Office Space Downtown',
                'tagline' => 'Fully furnished workspace in the city centre',
                'description' => 'Fully furnished office space in the heart of downtown. High-speed internet, meeting rooms, and 24/7 access.',
                'overview' => 'Downtown office rental',
                'key_features' => 'Furnished, meeting rooms, 24/7 access, high-speed internet',
                'what_makes_special' => 'Prime downtown location',
                'why_sponsored' => 'New commercial listing push',
                'additional_notes' => null,
                'advert_type' => 'Property',
                'category_id' => $categoryId,
                'country' => 'United States',
                'city' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'location_precision' => 'approximate',
                'price' => 5000.00,
                'currency' => 'USD',
                'condition' => 'used',
                'main_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                'additional_images' => json_encode([]),
                'video_link' => null,
                'seller_name' => 'NYC Office Rentals',
                'business_name' => 'NYC Office Rentals Inc.',
                'phone' => '+12125551234',
                'email' => 'info@nycoffice.com',
                'website' => null,
                'social_links' => json_encode([]),
                'logo' => null,
                'verified_seller' => true,
                'sponsorship_tier' => 'plus',
                'sponsorship_price' => 300.00,
                'payment_status' => 'paid',
                'payment_transaction_id' => 'txn_seed_office_002',
                'sponsorship_start_date' => Carbon::now()->subDays(1),
                'sponsorship_end_date' => Carbon::now()->addDays(29),
                'views_count' => 8500,
                'saves_count' => 95,
                'inquiries_count' => 22,
                'rating' => 4.5,
                'rating_count' => 18,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
                'slug' => 'modern-office-space-downtown',
                'tags' => json_encode(['office', 'nyc', 'commercial']),
                'seo_meta' => json_encode(['title' => 'Modern Office Space Downtown']),
                'created_by' => $userId,
                'updated_by' => $userId,
                'user_id' => $userId,
            ],
            [
                'title' => 'Professional Photography Services',
                'tagline' => 'Weddings, corporate, and portraits',
                'description' => 'Award-winning photographer available for weddings, corporate events, and portraits. Professional equipment and editing services.',
                'overview' => 'Professional photography service',
                'key_features' => 'Weddings, corporate, portraits, editing included',
                'what_makes_special' => 'Award-winning portfolio',
                'why_sponsored' => 'Seasonal booking campaign',
                'additional_notes' => null,
                'advert_type' => 'Service',
                'category_id' => $categoryId,
                'country' => 'United Kingdom',
                'city' => 'London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'location_precision' => 'approximate',
                'price' => 1500.00,
                'currency' => 'GBP',
                'condition' => 'not_applicable',
                'main_image' => 'https://images.unsplash.com/photo-1542037104857-ffbb0b9155fb?auto=format&fit=crop&w=1200&q=80',
                'additional_images' => json_encode([]),
                'video_link' => null,
                'seller_name' => 'Creative Lens Photography',
                'business_name' => 'Creative Lens Ltd',
                'phone' => '+447207123456',
                'email' => 'hello@creativelens.com',
                'website' => null,
                'social_links' => json_encode([]),
                'logo' => null,
                'verified_seller' => false,
                'sponsorship_tier' => 'basic',
                'sponsorship_price' => 99.00,
                'payment_status' => 'paid',
                'payment_transaction_id' => 'txn_seed_photo_003',
                'sponsorship_start_date' => Carbon::now(),
                'sponsorship_end_date' => Carbon::now()->addDays(30),
                'views_count' => 3200,
                'saves_count' => 41,
                'inquiries_count' => 12,
                'rating' => 4.8,
                'rating_count' => 27,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'slug' => 'professional-photography-services',
                'tags' => json_encode(['photography', 'london', 'service']),
                'seo_meta' => json_encode(['title' => 'Professional Photography Services']),
                'created_by' => $userId,
                'updated_by' => $userId,
                'user_id' => $userId,
            ],
        ];

        foreach ($adverts as $advert) {
            $exists = DB::table('sponsored_adverts')->where('slug', $advert['slug'])->exists();
            if ($exists) {
                continue;
            }

            DB::table('sponsored_adverts')->insert(array_merge($advert, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
