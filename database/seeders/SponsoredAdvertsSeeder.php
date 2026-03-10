<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SponsoredAdvertsSeeder extends Seeder
{
    public function run()
    {
        // Create Sponsored Adverts
        $adverts = [
            [
                'title' => 'Premium Smartphone Deal',
                'tagline' => 'Latest model at unbeatable price',
                'description' => 'Get the latest flagship smartphone with advanced features and stunning design. Limited time offer with free accessories.',
                'overview' => 'Premium smartphone with cutting-edge technology',
                'key_features' => '5G connectivity, Advanced camera system, All-day battery life',
                'what_makes_special' => 'Exclusive bundle deal with premium accessories',
                'why_sponsored' => 'Launch promotion for new flagship model',
                'additional_notes' => 'Limited stock available',
                'advert_type' => 'Product',
                'country' => 'United States',
                'city' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'location_precision' => 'exact',
                'price' => 899.00,
                'currency' => 'USD',
                'condition' => 'new',
                'main_image' => 'sponsored/smartphone-main.jpg',
                'additional_images' => json_encode(['sponsored/smartphone-1.jpg', 'sponsored/smartphone-2.jpg']),
                'video_link' => 'https://youtube.com/watch?v=smartphone-demo',
                'seller_name' => 'TechStore Premium',
                'business_name' => 'TechStore Inc.',
                'phone' => '+1234567890',
                'email' => 'premium@techstore.com',
                'website' => 'https://techstore.com/premium',
                'social_links' => json_encode([
                    'facebook' => 'https://facebook.com/techstore',
                    'twitter' => 'https://twitter.com/techstore'
                ]),
                'logo' => 'sponsored/techstore-logo.png',
                'verified_seller' => true,
                'sponsorship_tier' => 'premium',
                'sponsorship_price' => 500.00,
                'payment_status' => 'paid',
                'payment_transaction_id' => 'txn_sponsored_001',
                'sponsorship_start_date' => Carbon::now()->subDays(2),
                'sponsorship_end_date' => Carbon::now()->addDays(28),
                'views_count' => 1250,
                'saves_count' => 89,
                'inquiries_count' => 45,
                'rating' => 4.8,
                'rating_count' => 127,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'slug' => 'premium-smartphone-deal',
                'tags' => json_encode(['smartphone', 'tech', '5g', 'premium']),
                'seo_meta' => json_encode([
                    'title' => 'Premium Smartphone Deal - Latest Model',
                    'description' => 'Get the latest flagship smartphone at unbeatable price'
                ]),
                'created_by' => 2,
                'updated_by' => 2,
            ],
            [
                'title' => 'Luxury Watch Collection',
                'tagline' => 'Timeless elegance meets precision',
                'description' => 'Exclusive collection of luxury Swiss watches. Each timepiece represents the pinnacle of watchmaking excellence.',
                'overview' => 'Premium Swiss watch collection',
                'key_features' => 'Swiss movement, Sapphire crystal, Water resistance',
                'what_makes_special' => 'Limited edition pieces with certificates of authenticity',
                'why_sponsored' => 'Exclusive collection launch',
                'advert_type' => 'Product',
                'country' => 'Switzerland',
                'city' => 'Geneva',
                'latitude' => 46.2044,
                'longitude' => 6.1432,
                'location_precision' => 'exact',
                'price' => 5000.00,
                'currency' => 'CHF',
                'condition' => 'new',
                'main_image' => 'sponsored/watch-main.jpg',
                'additional_images' => json_encode(['sponsored/watch-1.jpg', 'sponsored/watch-2.jpg']),
                'seller_name' => 'Luxury Timepieces',
                'business_name' => 'Luxury Timepieces SA',
                'phone' => '+41223456789',
                'email' => 'info@luxurytimepieces.ch',
                'website' => 'https://luxurytimepieces.ch',
                'logo' => 'sponsored/luxury-logo.png',
                'verified_seller' => true,
                'sponsorship_tier' => 'premium',
                'sponsorship_price' => 750.00,
                'payment_status' => 'paid',
                'payment_transaction_id' => 'txn_sponsored_002',
                'sponsorship_start_date' => Carbon::now()->subDays(1),
                'sponsorship_end_date' => Carbon::now()->addDays(29),
                'views_count' => 890,
                'saves_count' => 67,
                'inquiries_count' => 23,
                'rating' => 4.9,
                'rating_count' => 45,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'slug' => 'luxury-watch-collection',
                'tags' => json_encode(['watch', 'luxury', 'swiss', 'timepiece']),
                'seo_meta' => json_encode([
                    'title' => 'Luxury Watch Collection - Swiss Timepieces',
                    'description' => 'Exclusive collection of luxury Swiss watches'
                ]),
                'created_by' => 3,
                'updated_by' => 3,
            ],
            [
                'title' => 'Professional Photography Course',
                'tagline' => 'Master the art of photography',
                'description' => 'Comprehensive online photography course covering everything from basics to advanced techniques. Learn from professional photographers.',
                'overview' => 'Professional photography training program',
                'key_features' => 'HD video lessons, Assignments, Personal feedback',
                'what_makes_special' => 'One-on-one mentoring sessions',
                'why_sponsored' => 'New course launch promotion',
                'advert_type' => 'Service',
                'country' => 'United Kingdom',
                'city' => 'London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'location_precision' => 'approximate',
                'price' => 299.00,
                'currency' => 'GBP',
                'condition' => 'not_applicable',
                'main_image' => 'sponsored/photography-course.jpg',
                'additional_images' => json_encode(['sponsored/course-1.jpg', 'sponsored/course-2.jpg']),
                'video_link' => 'https://youtube.com/watch?v=photography-intro',
                'seller_name' => 'Photo Academy',
                'business_name' => 'Photo Academy Ltd',
                'phone' => '+44201234567',
                'email' => 'courses@photoacademy.co.uk',
                'website' => 'https://photoacademy.co.uk',
                'logo' => 'sponsored/photoacademy-logo.png',
                'verified_seller' => true,
                'sponsorship_tier' => 'plus',
                'sponsorship_price' => 300.00,
                'payment_status' => 'paid',
                'payment_transaction_id' => 'txn_sponsored_003',
                'sponsorship_start_date' => Carbon::now(),
                'sponsorship_end_date' => Carbon::now()->addDays(14),
                'views_count' => 567,
                'saves_count' => 34,
                'inquiries_count' => 12,
                'rating' => 4.7,
                'rating_count' => 23,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'slug' => 'professional-photography-course',
                'tags' => json_encode(['photography', 'course', 'education', 'art']),
                'seo_meta' => json_encode([
                    'title' => 'Professional Photography Course - Learn Online',
                    'description' => 'Master photography with professional online course'
                ]),
                'created_by' => 4,
                'updated_by' => 4,
            ],
        ];

        foreach ($adverts as $advert) {
            DB::table('sponsored_adverts')->insert(array_merge($advert, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Featured Adverts
        $featured = [
            [
                'title' => 'Summer Fashion Collection',
                'description' => 'Trendy summer outfits at amazing prices',
                'price' => 49.99,
                'currency' => 'USD',
                'image' => 'featured/summer-fashion.jpg',
                'category' => 'Fashion',
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->addDays(25),
            ],
            [
                'title' => 'Home Gym Equipment Sale',
                'description' => 'Build your home gym with our premium equipment',
                'price' => 299.00,
                'currency' => 'USD',
                'image' => 'featured/gym-equipment.jpg',
                'category' => 'Fitness',
                'is_active' => true,
                'starts_at' => Carbon::now()->subDays(3),
                'ends_at' => Carbon::now()->addDays(17),
            ],
        ];

        foreach ($featured as $item) {
            DB::table('featured_adverts')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Sponsored Adverts seeder completed successfully!');
    }
}
