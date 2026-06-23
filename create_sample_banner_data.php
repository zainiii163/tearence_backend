<?php

require_once 'vendor/autoload.php';

use App\Models\BannerAd;
use App\Models\BannerCategory;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Create a sample category if it doesn't exist
    $category = BannerCategory::where('slug', 'technology')->first();
    if (!$category) {
        $category = BannerCategory::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Technology related banners',
            'is_active' => true
        ]);
        echo "Created category: Technology\n";
    }

    // Create sample banner ads
    $sampleBanners = [
        [
            'title' => 'Premium Tech Solutions',
            'slug' => 'premium-tech-solutions',
            'description' => 'Discover cutting-edge technology solutions for your business',
            'business_name' => 'TechCorp Inc.',
            'email' => 'contact@techcorp.com',
            'phone' => '+1-555-0123',
            'website_url' => 'https://techcorp.com',
            'banner_type' => 'image',
            'banner_size' => '728x90',
            'banner_image' => 'tech-banner-1.jpg',
            'destination_link' => 'https://techcorp.com/solutions',
            'call_to_action' => 'Learn More',
            'key_selling_points' => '24/7 Support\nExpert Team\nCutting-edge Technology',
            'offer_details' => 'Get 20% off on all enterprise solutions this month',
            'banner_category_id' => $category->id,
            'country' => 'United States',
            'city' => 'San Francisco',
            'target_countries' => json_encode(['United States', 'Canada', 'United Kingdom']),
            'target_audience' => json_encode(['Business Owners', 'IT Managers', 'Developers']),
            'promotion_tier' => 'featured',
            'promotion_price' => 50.00,
            'is_verified_business' => true,
            'status' => 'active',
            'is_active' => true,
            'views_count' => 1250,
            'clicks_count' => 187,
            'approved_at' => now()
        ],
        [
            'title' => 'Cloud Computing Services',
            'slug' => 'cloud-computing-services',
            'description' => 'Scalable cloud infrastructure for modern businesses',
            'business_name' => 'CloudNet Systems',
            'email' => 'sales@cloudnet.com',
            'banner_type' => 'image',
            'banner_size' => '300x250',
            'banner_image' => 'cloud-banner-2.jpg',
            'destination_link' => 'https://cloudnet.com/services',
            'call_to_action' => 'Start Free Trial',
            'banner_category_id' => $category->id,
            'country' => 'United States',
            'promotion_tier' => 'promoted',
            'promotion_price' => 25.00,
            'status' => 'active',
            'is_active' => true,
            'views_count' => 890,
            'clicks_count' => 134,
            'approved_at' => now()
        ],
        [
            'title' => 'AI-Powered Analytics',
            'slug' => 'ai-powered-analytics',
            'description' => 'Transform your data into actionable insights with AI',
            'business_name' => 'DataMind AI',
            'email' => 'info@datamind.ai',
            'phone' => '+1-555-0456',
            'website_url' => 'https://datamind.ai',
            'banner_type' => 'animated',
            'banner_size' => '160x600',
            'banner_image' => 'ai-banner-3.gif',
            'destination_link' => 'https://datamind.ai/analytics',
            'call_to_action' => 'Request Demo',
            'key_selling_points' => 'Real-time Processing\nMachine Learning\nCustom Dashboards',
            'banner_category_id' => $category->id,
            'country' => 'United States',
            'city' => 'New York',
            'target_countries' => json_encode(['United States', 'Germany', 'Japan']),
            'target_audience' => json_encode(['Data Scientists', 'Analysts', 'Executives']),
            'promotion_tier' => 'sponsored',
            'promotion_price' => 100.00,
            'is_verified_business' => true,
            'status' => 'active',
            'is_active' => true,
            'views_count' => 2100,
            'clicks_count' => 315,
            'approved_at' => now()
        ]
    ];

    foreach ($sampleBanners as $bannerData) {
        $existingBanner = BannerAd::where('slug', $bannerData['slug'])->first();
        if (!$existingBanner) {
            BannerAd::create($bannerData);
            echo "Created banner: {$bannerData['title']}\n";
        } else {
            echo "Banner already exists: {$bannerData['title']}\n";
        }
    }

    // Create another category
    $fashionCategory = BannerCategory::where('slug', 'fashion')->first();
    if (!$fashionCategory) {
        $fashionCategory = BannerCategory::create([
            'name' => 'Fashion & Style',
            'slug' => 'fashion',
            'description' => 'Fashion and lifestyle related banners',
            'is_active' => true
        ]);
        echo "Created category: Fashion & Style\n";
    }

    // Add a fashion banner
    $fashionBanner = [
        'title' => 'Summer Collection 2024',
        'slug' => 'summer-collection-2024',
        'description' => 'Discover the latest summer fashion trends',
        'business_name' => 'StyleHub',
        'email' => 'fashion@stylehub.com',
        'banner_type' => 'image',
        'banner_size' => '970x250',
        'banner_image' => 'fashion-banner-4.jpg',
        'destination_link' => 'https://stylehub.com/summer-2024',
        'call_to_action' => 'Shop Now',
        'banner_category_id' => $fashionCategory->id,
        'country' => 'United States',
        'promotion_tier' => 'standard',
        'status' => 'active',
        'is_active' => true,
        'views_count' => 567,
        'clicks_count' => 89,
        'approved_at' => now()
    ];

    $existingFashionBanner = BannerAd::where('slug', $fashionBanner['slug'])->first();
    if (!$existingFashionBanner) {
        BannerAd::create($fashionBanner);
        echo "Created banner: {$fashionBanner['title']}\n";
    } else {
        echo "Banner already exists: {$fashionBanner['title']}\n";
    }

    echo "\nSample banner data creation completed!\n";
    echo "Total banners: " . BannerAd::count() . "\n";
    echo "Total categories: " . BannerCategory::count() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
