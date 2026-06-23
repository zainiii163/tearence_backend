<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $banner = App\Models\BannerAd::create([
        'title' => 'Integration Test Banner',
        'slug' => 'integration-test-banner',
        'description' => 'Test banner to verify frontend-backend integration',
        'business_name' => 'Test Business',
        'contact_person' => 'John Doe',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'website_url' => 'https://example.com',
        'business_logo' => null,
        'banner_type' => 'image',
        'banner_size' => '728x90',
        'banner_image' => 'https://via.placeholder.com/728x90/4CAF50/FFFFFF?text=Test+Banner',
        'destination_link' => 'https://example.com',
        'call_to_action' => 'Click Here',
        'key_selling_points' => 'High visibility, Great reach',
        'offer_details' => 'Special promotion offer',
        'banner_category_id' => 1,
        'country' => 'USA',
        'city' => 'New York',
        'target_countries' => null,
        'target_audience' => null,
        'promotion_tier' => 'standard',
        'promotion_price' => 0,
        'promotion_start' => null,
        'promotion_end' => null,
        'is_verified_business' => false,
        'status' => 'active',
        'is_active' => true,
        'views_count' => 0,
        'clicks_count' => 0,
        'approved_at' => now(),
        'user_id' => null
    ]);
    
    echo "Banner created successfully with ID: " . $banner->id . "\n";
    echo "Title: " . $banner->title . "\n";
    echo "Status: " . $banner->status . "\n";
} catch (Exception $e) {
    echo "Error creating banner: " . $e->getMessage() . "\n";
}
