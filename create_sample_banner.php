<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $banner = App\Models\BannerAd::create([
        'title' => 'Premium Real Estate Services',
        'slug' => 'premium-real-estate-services',
        'description' => 'Find your dream home with our premium real estate services. We offer luxury properties, expert guidance, and seamless transactions.',
        'business_name' => 'Elite Properties',
        'contact_person' => 'Sarah Johnson',
        'email' => 'sarah@eliteproperties.com',
        'phone' => '+1 (555) 123-4567',
        'website_url' => 'https://eliteproperties.com',
        'business_logo' => null,
        'banner_type' => 'image',
        'banner_size' => '728x90',
        'banner_image' => 'https://via.placeholder.com/728x90/2563EB/FFFFFF?text=Elite+Properties+-+Premium+Real+Estate',
        'destination_link' => 'https://eliteproperties.com/listings',
        'call_to_action' => 'View Listings',
        'key_selling_points' => 'Luxury properties, Expert agents, 24/7 support, Free consultations',
        'offer_details' => 'Get 10% off commission on your first property listing',
        'banner_category_id' => 1,
        'country' => 'USA',
        'city' => 'Los Angeles',
        'target_countries' => '["USA", "Canada", "UK"]',
        'target_audience' => 'Home buyers, Real estate investors, Property sellers',
        'promotion_tier' => 'featured',
        'promotion_price' => 100,
        'promotion_start' => now(),
        'promotion_end' => now()->addDays(30),
        'is_verified_business' => true,
        'status' => 'active',
        'is_active' => true,
        'views_count' => 150,
        'clicks_count' => 25,
        'approved_at' => now(),
        'user_id' => null
    ]);
    
    echo "Banner created successfully!\n";
    echo "ID: " . $banner->id . "\n";
    echo "Title: " . $banner->title . "\n";
    echo "Business: " . $banner->business_name . "\n";
    echo "Category: " . $banner->banner_category_id . "\n";
    echo "Country: " . $banner->country . "\n";
    echo "Promotion Tier: " . $banner->promotion_tier . "\n";
    echo "Status: " . $banner->status . "\n";
    echo "Banner Image: " . $banner->banner_image . "\n";
    echo "Destination Link: " . $banner->destination_link . "\n";
    
    echo "\nBanner is now available at: http://127.0.0.1:8000/api/v1/banner-ads\n";
} catch (Exception $e) {
    echo "Error creating banner: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
