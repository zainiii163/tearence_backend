<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AffiliateSeeder extends Seeder
{
    public function run()
    {
        // Create Affiliate Posts
        $posts = [
            [
                'post_type' => 'business',
                'title' => 'Tech Affiliate Program',
                'tagline' => 'Earn 10% commission on tech products',
                'description' => 'Join our affiliate program and earn generous commissions on technology products including smartphones, laptops, and accessories.',
                'business_name' => 'TechAffiliate Pro',
                'commission_rate' => '10%',
                'cookie_duration' => 30,
                'allowed_traffic_types' => json_encode(['social', 'email', 'ppc']),
                'restrictions' => 'No brand bidding, no incentive traffic',
                'affiliate_link' => 'https://techaffiliate.com/join/12345',
                'business_email' => 'partners@techaffiliate.com',
                'website_url' => 'https://techaffiliate.com',
                'verification_document' => 'documents/tech-affiliate-verification.pdf',
                'target_audience' => 'Tech enthusiasts, bloggers',
                'hashtags' => json_encode(['tech', 'gadgets', 'affiliate', 'commission']),
                'country_region' => 'North America',
                'images' => json_encode(['affiliate/tech-program-1.jpg', 'affiliate/tech-program-2.jpg']),
                'promotional_assets' => json_encode(['banners/tech-728x90.jpg', 'banners/tech-300x250.jpg']),
                'customer_id' => 1, // Will be updated when customers table exists
                'category_id' => 1, // Will be updated when categories table exists
                'upsell_tier' => 'featured',
                'status' => 'approved',
                'is_active' => true,
                'approved_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addDays(30),
            ],
            [
                'post_type' => 'promoter',
                'title' => 'Fashion Influencer Network',
                'tagline' => 'Connect with top fashion brands',
                'description' => 'Join our network of fashion influencers and collaborate with leading fashion brands for sponsored content and affiliate partnerships.',
                'target_audience' => 'Fashion influencers, style bloggers',
                'hashtags' => json_encode(['fashion', 'influencer', 'style', 'collaboration']),
                'country_region' => 'Europe',
                'images' => json_encode(['affiliate/fashion-network-1.jpg']),
                'promotional_assets' => json_encode(['guidelines/fashion-influencer-guide.pdf']),
                'customer_id' => 2,
                'category_id' => 2,
                'upsell_tier' => 'promoted',
                'status' => 'approved',
                'is_active' => true,
                'approved_at' => Carbon::now()->subDays(3),
                'expires_at' => Carbon::now()->addDays(60),
            ],
            [
                'post_type' => 'business',
                'title' => 'Travel Booking Affiliate',
                'tagline' => '5% commission on travel bookings',
                'description' => 'Earn commissions on flights, hotels, and vacation packages. Join our travel affiliate program today.',
                'business_name' => 'TravelBook Partners',
                'commission_rate' => '5%',
                'cookie_duration' => 45,
                'allowed_traffic_types' => json_encode(['social', 'email', 'content']),
                'restrictions' => 'No trademark bidding in PPC',
                'affiliate_link' => 'https://travelbook.com/affiliate/67890',
                'business_email' => 'affiliates@travelbook.com',
                'website_url' => 'https://travelbook.com',
                'target_audience' => 'Travel bloggers, content creators',
                'hashtags' => json_encode(['travel', 'booking', 'vacation', 'affiliate']),
                'country_region' => 'Global',
                'images' => json_encode(['affiliate/travel-program-1.jpg', 'affiliate/travel-program-2.jpg']),
                'promotional_assets' => json_encode(['banners/travel-160x600.jpg', 'banners/travel-728x90.jpg']),
                'customer_id' => 3,
                'category_id' => 3,
                'upsell_tier' => 'sponsored',
                'status' => 'pending',
                'is_active' => false,
                'expires_at' => Carbon::now()->addDays(90),
            ],
        ];

        foreach ($posts as $post) {
            DB::table('affiliate_posts')->insert(array_merge($post, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Affiliate Upsell Plans
        $plans = [
            [
                'name' => 'Basic Promotion',
                'description' => '7-day promotion in standard rotation',
                'price' => 25.00,
                'currency' => 'USD',
                'duration_days' => 7,
                'features' => json_encode([
                    'Standard placement',
                    'Basic analytics',
                    'Email support'
                ]),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Featured Promotion',
                'description' => '14-day featured placement with enhanced visibility',
                'price' => 75.00,
                'currency' => 'USD',
                'duration_days' => 14,
                'features' => json_encode([
                    'Featured placement',
                    'Enhanced analytics',
                    'Priority support',
                    'Social media promotion'
                ]),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Premium Network',
                'description' => '30-day premium placement across all channels',
                'price' => 200.00,
                'currency' => 'USD',
                'duration_days' => 30,
                'features' => json_encode([
                    'Premium placement',
                    'Advanced analytics',
                    'Dedicated support',
                    'Multi-channel promotion',
                    'Custom creatives'
                ]),
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('affiliate_upsell_plans')->insert(array_merge($plan, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Affiliate Post Upsells
        $upsells = [
            [
                'affiliate_post_id' => 1,
                'upsell_plan_id' => 2,
                'customer_id' => 1,
                'amount_paid' => 75.00,
                'currency' => 'USD',
                'payment_method' => 'stripe',
                'transaction_id' => 'txn_affiliate_001',
                'payment_status' => 'paid',
                'starts_at' => Carbon::now()->subDays(2),
                'ends_at' => Carbon::now()->addDays(12),
                'is_active' => true,
            ],
            [
                'affiliate_post_id' => 2,
                'upsell_plan_id' => 1,
                'customer_id' => 2,
                'amount_paid' => 25.00,
                'currency' => 'USD',
                'payment_method' => 'paypal',
                'transaction_id' => 'txn_affiliate_002',
                'payment_status' => 'paid',
                'starts_at' => Carbon::now()->subDays(1),
                'ends_at' => Carbon::now()->addDays(6),
                'is_active' => true,
            ],
            [
                'affiliate_post_id' => 3,
                'upsell_plan_id' => 3,
                'customer_id' => 3,
                'amount_paid' => 200.00,
                'currency' => 'USD',
                'payment_method' => 'stripe',
                'transaction_id' => 'txn_affiliate_003',
                'payment_status' => 'pending',
                'starts_at' => Carbon::now()->addDays(1),
                'ends_at' => Carbon::now()->addDays(31),
                'is_active' => false,
            ],
        ];

        foreach ($upsells as $upsell) {
            DB::table('affiliate_post_upsells')->insert(array_merge($upsell, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Affiliate seeder completed successfully!');
    }
}
