<?php

namespace Database\Seeders;

use App\Models\BannerAd;
use App\Models\BannerCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Clive: paid marketing banners for every banner category.
 * Images live on the frontend CDN: /img/banners/marketplace/*
 */
class CategoryPaidBannersSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('user_id') ?? User::query()->value('id');
        if (!$userId) {
            $this->command?->warn('CategoryPaidBannersSeeder: no users — skip.');
            return;
        }

        $frontend = rtrim(env('FRONTEND_URL', 'https://worldwideadverts.info'), '/');

        $packs = [
            'real-estate' => ['Luxury Homes Hero', 'Prime Listings Leaderboard', 'Open House Rectangle', 'Property Showcase Billboard'],
            'vehicles' => ['Drive Deals Hero', 'Auto Sale Leaderboard', 'Certified Cars Rectangle', 'Dealership Billboard'],
            'travel-resorts' => ['Escape Hero', 'Resort Escape Leaderboard', 'Beach Getaway Rectangle', 'Holiday Package Billboard'],
            'jobs-recruitment' => ['Hire Faster Hero', 'Careers Leaderboard', 'Now Hiring Rectangle', 'Recruitment Drive Billboard'],
            'books-authors' => ['New Release Hero', 'Author Spotlight Leaderboard', 'Bestsellers Rectangle', 'Book Launch Billboard'],
            'services' => ['Trusted Pros Hero', 'Book a Pro Leaderboard', 'Local Services Rectangle', 'Professional Services Billboard'],
            'events' => ['Tonight Events Hero', 'Ticket Drop Leaderboard', 'Conference Rectangle', 'Festival Billboard'],
            'food-hospitality' => ['Taste Hero', 'Restaurant Promo Leaderboard', 'Chef Special Rectangle', 'Dining Night Billboard'],
            'fashion-beauty' => ['Style Fashion Hero', 'New Collection Leaderboard', 'Beauty Drop Rectangle', 'Runway Sale Billboard'],
            'tech-electronics' => ['Next-Gen Tech Hero', 'Gadget Deals Leaderboard', 'Electronics Rectangle', 'Launch Day Billboard'],
            'health-wellness' => ['Wellness Hero', 'Feel Better Leaderboard', 'Fitness Offer Rectangle', 'Clinic Promo Billboard'],
            'business-finance' => ['Grow Finance Hero', 'Invest Smart Leaderboard', 'Finance Tips Rectangle', 'Business Growth Billboard'],
        ];

        $sizes = [
            ['key' => 'hero', 'size' => '1200x628', 'price' => 49, 'tier' => 'sponsored', 'ext' => 'png', 'prefix' => 'banner-'],
            ['key' => 'leaderboard', 'size' => '728x90', 'price' => 29, 'tier' => 'featured', 'ext' => 'svg', 'prefix' => ''],
            ['key' => 'rectangle', 'size' => '300x250', 'price' => 35, 'tier' => 'featured', 'ext' => 'svg', 'prefix' => ''],
            ['key' => 'billboard', 'size' => '970x250', 'price' => 59, 'tier' => 'sponsored', 'ext' => 'svg', 'prefix' => ''],
        ];

        $created = 0;

        foreach ($packs as $slug => $titles) {
            $category = BannerCategory::where('slug', $slug)->first();
            if (!$category) {
                $this->command?->warn("Missing category slug: {$slug}");
                continue;
            }

            foreach ($sizes as $i => $size) {
                $title = $titles[$i] ?? ($category->name.' '.$size['key'].' Banner');
                $file = $size['ext'] === 'png'
                    ? "banner-{$slug}.png"
                    : "{$slug}-{$size['key']}.svg";
                $imageUrl = "{$frontend}/img/banners/marketplace/{$file}";
                $uniqueSlug = 'wwa-paid-'.$slug.'-'.$size['key'];

                BannerAd::updateOrCreate(
                    ['slug' => $uniqueSlug],
                    [
                        'title' => $title,
                        'description' => "Paid {$size['size']} banner for {$category->name}. Buy to download and use in your marketing.",
                        'business_name' => 'WWA Banner Studio',
                        'contact_person' => 'WWA Sales',
                        'email' => 'banners@worldwideadverts.info',
                        'website_url' => $frontend.'/banner-adverts',
                        'banner_type' => 'image',
                        'banner_size' => $size['size'],
                        'banner_image' => $imageUrl,
                        'destination_link' => $imageUrl,
                        'call_to_action' => 'Buy & download',
                        'key_selling_points' => 'Ready-to-use creative, category-specific, paid download',
                        'validity_start' => now()->subDay(),
                        'validity_end' => now()->addYear(),
                        'banner_category_id' => $category->id,
                        'country' => 'Global',
                        'city' => 'Worldwide',
                        'target_countries' => ['Global'],
                        'promotion_tier' => $size['tier'],
                        'promotion_price' => $size['price'],
                        'promotion_start' => now()->subDay(),
                        'promotion_end' => now()->addMonths(6),
                        'is_verified_business' => true,
                        'status' => 'active',
                        'is_active' => true,
                        'views_count' => rand(500, 5000),
                        'clicks_count' => rand(40, 400),
                        'approved_at' => now()->subDay(),
                        'user_id' => $userId,
                    ]
                );
                $created++;
            }
        }

        $this->command?->info("CategoryPaidBannersSeeder: upserted {$created} paid banners.");
    }
}
