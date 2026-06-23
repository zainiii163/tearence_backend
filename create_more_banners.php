<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$banners = [
    [
        'title' => 'Digital Marketing Solutions',
        'slug' => 'digital-marketing-solutions',
        'description' => 'Boost your online presence with our comprehensive digital marketing services including SEO, PPC, and social media marketing.',
        'business_name' => 'GrowthHub Digital',
        'contact_person' => 'Michael Chen',
        'email' => 'michael@growthhub.com',
        'phone' => '+1 (555) 987-6543',
        'website_url' => 'https://growthhub.com',
        'business_logo' => null,
        'banner_type' => 'image',
        'banner_size' => '300x250',
        'banner_image' => 'https://via.placeholder.com/300x250/10B981/FFFFFF?text=GrowthHub+Digital',
        'destination_link' => 'https://growthhub.com/services',
        'call_to_action' => 'Get Started',
        'key_selling_points' => 'SEO optimization, PPC campaigns, Social media management, Analytics reporting',
        'offer_details' => 'Free marketing audit for new clients',
        'banner_category_id' => 2,
        'country' => 'UK',
        'city' => 'London',
        'target_countries' => '["UK", "USA", "Germany"]',
        'target_audience' => 'Small businesses, Startups, E-commerce companies',
        'promotion_tier' => 'promoted',
        'promotion_price' => 50,
        'promotion_start' => now(),
        'promotion_end' => now()->addDays(30),
        'is_verified_business' => true,
        'status' => 'active',
        'is_active' => true,
        'views_count' => 320,
        'clicks_count' => 48,
        'approved_at' => now(),
        'user_id' => null
    ],
    [
        'title' => 'Tech Startup Funding',
        'slug' => 'tech-startup-funding',
        'description' => 'Secure funding for your innovative tech startup. We connect entrepreneurs with investors and provide expert guidance.',
        'business_name' => 'Venture Capital Partners',
        'contact_person' => 'Emily Roberts',
        'email' => 'emily@vcp.com',
        'phone' => '+1 (555) 456-7890',
        'website_url' => 'https://vcp.com',
        'business_logo' => null,
        'banner_type' => 'image',
        'banner_size' => '728x90',
        'banner_image' => 'https://via.placeholder.com/728x90/8B5CF6/FFFFFF?text=Venture+Capital+Partners',
        'destination_link' => 'https://vcp.com/apply',
        'call_to_action' => 'Apply Now',
        'key_selling_points' => 'Quick funding decisions, Expert mentorship, Large network of investors, Flexible terms',
        'offer_details' => 'No fees for first 6 months',
        'banner_category_id' => 3,
        'country' => 'USA',
        'city' => 'San Francisco',
        'target_countries' => '["USA", "Canada"]',
        'target_audience' => 'Tech founders, Startup teams, Early-stage companies',
        'promotion_tier' => 'sponsored',
        'promotion_price' => 200,
        'promotion_start' => now(),
        'promotion_end' => now()->addDays(30),
        'is_verified_business' => true,
        'status' => 'active',
        'is_active' => true,
        'views_count' => 580,
        'clicks_count' => 95,
        'approved_at' => now(),
        'user_id' => null
    ],
    [
        'title' => 'E-commerce Platform',
        'slug' => 'e-commerce-platform',
        'description' => 'Build your online store with our powerful e-commerce platform. Easy setup, secure payments, and beautiful templates.',
        'business_name' => 'ShopEasy',
        'contact_person' => 'David Kim',
        'email' => 'david@shopeasy.com',
        'phone' => '+1 (555) 234-5678',
        'website_url' => 'https://shopeasy.com',
        'business_logo' => null,
        'banner_type' => 'image',
        'banner_size' => '160x600',
        'banner_image' => 'https://via.placeholder.com/160x600/F59E0B/FFFFFF?text=ShopEasy',
        'destination_link' => 'https://shopeasy.com/trial',
        'call_to_action' => 'Free Trial',
        'key_selling_points' => 'Drag-and-drop builder, 24/7 support, SEO optimized, Mobile responsive',
        'offer_details' => '30-day free trial, no credit card required',
        'banner_category_id' => 4,
        'country' => 'Canada',
        'city' => 'Toronto',
        'target_countries' => '["Canada", "USA", "UK", "Australia"]',
        'target_audience' => 'Small business owners, Retailers, Artisans',
        'promotion_tier' => 'standard',
        'promotion_price' => 0,
        'promotion_start' => null,
        'promotion_end' => null,
        'is_verified_business' => false,
        'status' => 'active',
        'is_active' => true,
        'views_count' => 210,
        'clicks_count' => 32,
        'approved_at' => now(),
        'user_id' => null
    ]
];

$createdCount = 0;
foreach ($banners as $bannerData) {
    try {
        $banner = App\Models\BannerAd::create($bannerData);
        echo "✓ Created banner: " . $banner->title . " (ID: " . $banner->id . ")\n";
        $createdCount++;
    } catch (Exception $e) {
        echo "✗ Error creating banner: " . $e->getMessage() . "\n";
    }
}

echo "\nTotal banners created: " . $createdCount . "\n";
echo "All banners are now available at: http://127.0.0.1:8000/api/v1/banner-ads\n";
