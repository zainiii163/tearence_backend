<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== Testing Banner Ads API Response ===\n\n";

// Simulate API request to /api/v1/banner-ads
$controller = new App\Http\Controllers\Api\BannerAdController();
$request = Illuminate\Http\Request::create('/api/v1/banner-ads', 'GET');

try {
    $response = $controller->index($request);
    $data = json_decode($response->getContent(), true);

    echo "API Response Status: " . $response->getStatusCode() . "\n";
    echo "Success: " . ($data['success'] ?? 'N/A') . "\n";
    echo "Total Banners in Response: " . count($data['data'] ?? []) . "\n";
    echo "Meta Total: " . ($data['meta']['total'] ?? 'N/A') . "\n";
    echo "Meta Per Page: " . ($data['meta']['per_page'] ?? 'N/A') . "\n";
    echo "Meta Current Page: " . ($data['meta']['current_page'] ?? 'N/A') . "\n\n";

    echo "=== Banner Data from API ===\n\n";
    if (isset($data['data']) && is_array($data['data'])) {
        foreach ($data['data'] as $banner) {
            echo "ID: " . ($banner['id'] ?? 'N/A') . "\n";
            echo "Title: " . ($banner['title'] ?? 'N/A') . "\n";
            echo "Slug: " . ($banner['slug'] ?? 'N/A') . "\n";
            echo "Status: " . ($banner['status'] ?? 'N/A') . "\n";
            echo "Business Name: " . ($banner['business_name'] ?? 'N/A') . "\n";
            echo "Banner Type: " . ($banner['banner_type'] ?? 'N/A') . "\n";
            echo "Banner Size: " . ($banner['banner_size'] ?? 'N/A') . "\n";
            echo "Views Count: " . ($banner['views_count'] ?? 'N/A') . "\n";
            echo "Clicks Count: " . ($banner['clicks_count'] ?? 'N/A') . "\n";
            echo "Promotion Tier: " . ($banner['promotion_tier'] ?? 'N/A') . "\n";
            echo "------------------------\n";
        }
    } else {
        echo "No banner data in API response\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
