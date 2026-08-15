<?php

namespace Database\Seeders;

use App\Models\AffiliateCategory;
use App\Models\BusinessAffiliateOffer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds realistic ClickBank-style product/service affiliate programs
 * for World Wide Adverts marketplace.
 *
 * Run: php artisan db:seed --class=AffiliateMarketplaceOffersSeeder
 */
class AffiliateMarketplaceOffersSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::query()->orderBy('user_id')->first();
        if (!$seller) {
            $this->command?->error('No users found. Create a user before seeding offers.');
            return;
        }

        $categories = AffiliateCategory::query()->pluck('id', 'slug');
        if ($categories->isEmpty()) {
            $this->command?->error('No affiliate categories. Run AffiliateSystemSeeder first.');
            return;
        }

        $cat = function (string $slug, int $fallbackId) use ($categories) {
            return (int) ($categories[$slug] ?? $fallbackId);
        };

        // Soft-hide placeholder/lorem offers so marketplace shows real programs
        BusinessAffiliateOffer::query()
            ->where(function ($q) {
                $q->where('product_service_title', 'like', '%Excepturi%')
                    ->orWhere('product_service_title', 'like', '%Laboris%')
                    ->orWhere('business_name', 'like', '%Neque%')
                    ->orWhere('business_name', 'like', '%Modi alias%');
            })
            ->update([
                'status' => 'rejected',
                'is_active' => false,
            ]);

        $hasJoinInstructions = Schema::hasColumn('business_affiliate_offers', 'join_instructions');

        $offers = [
            [
                'slug_key' => 'nova-crm-suite',
                'affiliate_category_id' => $cat('software-saas', 10),
                'business_name' => 'NovaDesk Software',
                'product_service_title' => 'Nova CRM Suite — Sales Pipeline for SMBs',
                'tagline' => 'Close more deals with a simple CRM built for small teams',
                'description' => "Nova CRM Suite helps service businesses track leads, follow-ups, and invoices in one place.\n\nPromote the free trial. You earn when businesses subscribe to Pro or Growth plans. Landing pages, email swipe, and banners included.",
                'country' => 'United Kingdom',
                'commission_type' => 'percentage',
                'commission_rate' => 40,
                'cookie_duration' => 60,
                'price' => 49,
                'tracking_link' => 'https://worldwideadverts.info/services',
                'website_url' => 'https://worldwideadverts.info',
                'business_email' => 'affiliates@worldwideadverts.info',
                'is_featured' => true,
                'is_verified' => true,
                'is_promoted' => true,
                'views' => 420,
                'clicks' => 186,
                'applications' => 12,
                'assets' => [
                    'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&q=80',
                    'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80',
                ],
                'join' => 'Share your hop link on LinkedIn, YouTube reviews, or Google Ads (no brand bidding). Payouts monthly via PayPal.',
            ],
            [
                'slug_key' => 'brightpath-tutoring',
                'affiliate_category_id' => $cat('education-courses', 6),
                'business_name' => 'BrightPath Learning',
                'product_service_title' => 'Online Math Tutoring (1:1 Live Sessions)',
                'tagline' => 'Book live tutors for GCSE, SAT, and university prep',
                'description' => "BrightPath connects students with vetted tutors for live online lessons.\n\nAffiliates earn on first paid package purchase. Great for parenting blogs, education YouTube, and school Facebook groups.",
                'country' => 'United States',
                'commission_type' => 'fixed',
                'commission_rate' => 35,
                'cookie_duration' => 30,
                'price' => 120,
                'tracking_link' => 'https://brightpath.example/book',
                'website_url' => 'https://brightpath.example',
                'business_email' => 'affiliates@brightpath.example',
                'is_featured' => true,
                'is_verified' => true,
                'views' => 310,
                'clicks' => 98,
                'applications' => 8,
                'assets' => [
                    'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=1200&q=80',
                ],
                'join' => 'Promote free assessment booking. Cookie lasts 30 days. No misleading “guaranteed grades” claims.',
            ],
            [
                'slug_key' => 'harbor-home-clean',
                'affiliate_category_id' => $cat('business-services', 12),
                'business_name' => 'Harbor Home Care',
                'product_service_title' => 'Professional Home Cleaning Service',
                'tagline' => 'Book trusted cleaners for homes and Airbnbs',
                'description' => "Harbor Home Care offers recurring and deep-clean packages in major cities.\n\nEarn a fixed bounty for each completed first booking referred through your hop link.",
                'country' => 'Canada',
                'commission_type' => 'fixed',
                'commission_rate' => 25,
                'cookie_duration' => 14,
                'price' => 89,
                'tracking_link' => 'https://harborhome.example/book',
                'website_url' => 'https://harborhome.example',
                'business_email' => 'partners@harborhome.example',
                'is_sponsored' => true,
                'is_verified' => true,
                'views' => 255,
                'clicks' => 74,
                'applications' => 5,
                'assets' => [
                    'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=1200&q=80',
                ],
                'join' => 'Local Facebook groups and Google LSA-style content work well. Commission paid after first completed job.',
            ],
            [
                'slug_key' => 'vitaflow-wellness',
                'affiliate_category_id' => $cat('health-wellness', 5),
                'business_name' => 'VitaFlow Wellness',
                'product_service_title' => 'VitaFlow Daily Wellness Subscription',
                'tagline' => 'Science-backed supplements with monthly delivery',
                'description' => "VitaFlow is a wellness subscription with vitamins and recovery blends.\n\nHigh recurring commission on subscription months 1–12. Creatives include lifestyle photos and short video scripts.",
                'country' => 'United States',
                'commission_type' => 'percentage',
                'commission_rate' => 30,
                'cookie_duration' => 45,
                'price' => 39,
                'tracking_link' => 'https://vitaflow.example/subscribe',
                'website_url' => 'https://vitaflow.example',
                'business_email' => 'affiliates@vitaflow.example',
                'is_featured' => true,
                'is_promoted' => true,
                'is_verified' => true,
                'views' => 890,
                'clicks' => 340,
                'applications' => 24,
                'assets' => [
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=1200&q=80',
                    'https://images.unsplash.com/photo-1505576399279-565b52d4ac71?w=1200&q=80',
                ],
                'join' => 'Health claims must match product labels. Influencer and email traffic preferred. No spam.',
            ],
            [
                'slug_key' => 'skyline-stays',
                'affiliate_category_id' => $cat('travel-tourism', 3),
                'business_name' => 'Skyline Stays',
                'product_service_title' => 'Boutique Hotel Booking — City Escapes',
                'tagline' => 'Curated boutique hotels with member-only rates',
                'description' => "Skyline Stays lists boutique hotels and short city breaks.\n\nEarn % of the booking value when a guest completes a stay. Ideal for travel creators and newsletters.",
                'country' => 'United Arab Emirates',
                'commission_type' => 'percentage',
                'commission_rate' => 12,
                'cookie_duration' => 7,
                'price' => 180,
                'tracking_link' => 'https://skylinestays.example/search',
                'website_url' => 'https://skylinestays.example',
                'business_email' => 'affiliates@skylinestays.example',
                'is_verified' => true,
                'views' => 512,
                'clicks' => 201,
                'applications' => 9,
                'assets' => [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80',
                ],
                'join' => 'Commission locks after checkout + no-show window. Use provided hotel photography only.',
            ],
            [
                'slug_key' => 'ledgerly-bookkeeping',
                'affiliate_category_id' => $cat('finance-insurance', 4),
                'business_name' => 'Ledgerly Accounting',
                'product_service_title' => 'Remote Bookkeeping for Online Sellers',
                'tagline' => 'Monthly bookkeeping for Shopify & Amazon sellers',
                'description' => "Ledgerly provides remote bookkeepers for e-commerce brands.\n\nFixed bounty when a seller starts a paid monthly plan. Strong fit for ecommerce podcasts and SaaS comparison posts.",
                'country' => 'United Kingdom',
                'commission_type' => 'fixed',
                'commission_rate' => 75,
                'cookie_duration' => 90,
                'price' => 199,
                'tracking_link' => 'https://ledgerly.example/start',
                'website_url' => 'https://ledgerly.example',
                'business_email' => 'partners@ledgerly.example',
                'is_featured' => true,
                'is_verified' => true,
                'views' => 268,
                'clicks' => 91,
                'applications' => 6,
                'assets' => [
                    'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=1200&q=80',
                ],
                'join' => 'Do not imply tax-filing guarantees. Bounty paid after first invoice is collected.',
            ],
            [
                'slug_key' => 'glowatelier-skincare',
                'affiliate_category_id' => $cat('fashion-beauty', 2),
                'business_name' => 'Glow Atelier',
                'product_service_title' => 'Glow Atelier Clean Skincare Kit',
                'tagline' => 'Dermatologist-formulated starter kit',
                'description' => "A 4-step clean skincare kit for sensitive skin.\n\nPercentage commission on product orders. Creatives include before/after friendly lifestyle shots (no medical claims).",
                'country' => 'France',
                'commission_type' => 'percentage',
                'commission_rate' => 25,
                'cookie_duration' => 30,
                'price' => 68,
                'tracking_link' => 'https://glowatelier.example/kit',
                'website_url' => 'https://glowatelier.example',
                'business_email' => 'collab@glowatelier.example',
                'is_promoted' => true,
                'is_verified' => true,
                'views' => 640,
                'clicks' => 220,
                'applications' => 15,
                'assets' => [
                    'https://images.unsplash.com/photo-1556228578-0d85b1a4d571?w=1200&q=80',
                ],
                'join' => 'Instagram Reels and TikTok allowed. No “cure acne overnight” claims.',
            ],
            [
                'slug_key' => 'fixcraft-workshops',
                'affiliate_category_id' => $cat('education-courses', 6),
                'business_name' => 'ForgeCraft Academy',
                'product_service_title' => 'Weekend Digital Marketing Workshops',
                'tagline' => 'Live weekend workshops for founders & freelancers',
                'description' => "In-person and Zoom workshops covering ads, SEO, and funnels.\n\nEarn fixed commission per paid seat. Perfect for community organizers and LinkedIn creators.",
                'country' => 'Germany',
                'commission_type' => 'fixed',
                'commission_rate' => 40,
                'cookie_duration' => 21,
                'price' => 249,
                'tracking_link' => 'https://forgecraft.example/workshops',
                'website_url' => 'https://forgecraft.example',
                'business_email' => 'affiliates@forgecraft.example',
                'is_verified' => true,
                'views' => 190,
                'clicks' => 67,
                'applications' => 4,
                'assets' => [
                    'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1200&q=80',
                ],
                'join' => 'Share workshop calendars. Commission after seat payment clears.',
            ],
            [
                'slug_key' => 'greenlot-garden',
                'affiliate_category_id' => $cat('home-garden', 7),
                'business_name' => 'GreenLot Gardens',
                'product_service_title' => 'Smart Garden Subscription Boxes',
                'tagline' => 'Seasonal plants + soil kits delivered monthly',
                'description' => "GreenLot delivers seasonal gardening kits with QR care guides.\n\nRecurring % on subscription boxes. Strong for home/DIY creators.",
                'country' => 'Netherlands',
                'commission_type' => 'percentage',
                'commission_rate' => 20,
                'cookie_duration' => 30,
                'price' => 32,
                'tracking_link' => 'https://greenlot.example/box',
                'website_url' => 'https://greenlot.example',
                'business_email' => 'grow@greenlot.example',
                'is_sponsored' => true,
                'views' => 145,
                'clicks' => 52,
                'applications' => 3,
                'assets' => [
                    'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=1200&q=80',
                ],
                'join' => 'Use provided plant photography. Shipping only to EU for now.',
            ],
            [
                'slug_key' => 'autopilot-detail',
                'affiliate_category_id' => $cat('automotive', 8),
                'business_name' => 'AutoPilot Detail Co',
                'product_service_title' => 'Mobile Car Detailing Service',
                'tagline' => 'We come to your driveway — premium detail packages',
                'description' => "Mobile detailing for cars and vans. Packages from express wash to ceramic coat prep.\n\nFixed bounty per completed first booking in your city coverage zone.",
                'country' => 'Australia',
                'commission_type' => 'fixed',
                'commission_rate' => 20,
                'cookie_duration' => 14,
                'price' => 99,
                'tracking_link' => 'https://autopilotdetail.example/book',
                'website_url' => 'https://autopilotdetail.example',
                'business_email' => 'partners@autopilotdetail.example',
                'is_verified' => true,
                'views' => 210,
                'clicks' => 88,
                'applications' => 7,
                'assets' => [
                    'https://images.unsplash.com/photo-1601362840469-51e4d8d58785?w=1200&q=80',
                ],
                'join' => 'Geo-target Sydney / Melbourne creatives. Paid after job completion.',
            ],
        ];

        $created = 0;
        $updated = 0;

        foreach ($offers as $row) {
            $payload = [
                'user_id' => $seller->user_id,
                'affiliate_category_id' => $row['affiliate_category_id'],
                'business_name' => $row['business_name'],
                'product_service_title' => $row['product_service_title'],
                'tagline' => $row['tagline'],
                'description' => $row['description'],
                'country' => $row['country'],
                'region' => null,
                'commission_type' => $row['commission_type'],
                'commission_rate' => $row['commission_rate'],
                'cookie_duration' => $row['cookie_duration'],
                'allowed_traffic_types' => ['social_media', 'email', 'blogging', 'influencer', 'ppc'],
                'restrictions' => 'No trademark bidding. No incentivized spam traffic.',
                'tracking_link' => $row['tracking_link'],
                'promotional_assets' => $row['assets'],
                'business_email' => $row['business_email'],
                'website_url' => $row['website_url'],
                'is_verified' => (bool) ($row['is_verified'] ?? false),
                'status' => 'approved',
                'is_promoted' => (bool) ($row['is_promoted'] ?? false),
                'is_featured' => (bool) ($row['is_featured'] ?? false),
                'is_sponsored' => (bool) ($row['is_sponsored'] ?? false),
                'price' => $row['price'],
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(3),
                'is_active' => true,
                'views' => $row['views'],
                'clicks' => $row['clicks'],
                'applications' => $row['applications'],
            ];

            if ($hasJoinInstructions) {
                $payload['join_instructions'] = $row['join'];
            }

            $existing = BusinessAffiliateOffer::query()
                ->where(function ($q) use ($row) {
                    $q->where('product_service_title', $row['product_service_title'])
                        ->orWhere('tracking_link', $row['tracking_link']);
                })
                ->first();

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                BusinessAffiliateOffer::create($payload);
                $created++;
            }
        }

        $this->command?->info("Affiliate marketplace offers: created {$created}, updated {$updated}.");
    }
}
