<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Service Endpoints Diagnostic ===\n\n";

// Test 1: Check if service_categories table exists
echo "1. Checking service_categories table...\n";
try {
    $categoriesCount = \DB::table('service_categories')->count();
    echo "   ✓ service_categories table exists ({$categoriesCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ service_categories table ERROR: " . $e->getMessage() . "\n";
}

// Test 2: Check if services table exists
echo "\n2. Checking services table...\n";
try {
    $servicesCount = \DB::table('services')->count();
    echo "   ✓ services table exists ({$servicesCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ services table ERROR: " . $e->getMessage() . "\n";
}

// Test 3: Check if service_activities table exists
echo "\n3. Checking service_activities table...\n";
try {
    $activitiesCount = \DB::table('service_activities')->count();
    echo "   ✓ service_activities table exists ({$activitiesCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ service_activities table ERROR: " . $e->getMessage() . "\n";
}

// Test 4: Check ServiceCategory model
echo "\n4. Testing ServiceCategory model...\n";
try {
    $categories = \App\Models\ServiceCategory::where('is_active', true)->get();
    echo "   ✓ ServiceCategory model works ({$categories->count()} active categories)\n";
} catch (\Exception $e) {
    echo "   ✗ ServiceCategory model ERROR: " . $e->getMessage() . "\n";
}

// Test 5: Check Service model
echo "\n5. Testing Service model...\n";
try {
    $services = \App\Models\Service::with(['category'])->limit(1)->get();
    echo "   ✓ Service model works ({$services->count()} services)\n";
} catch (\Exception $e) {
    echo "   ✗ Service model ERROR: " . $e->getMessage() . "\n";
}

// Test 6: Check ServiceActivity model
echo "\n6. Testing ServiceActivity model...\n";
try {
    $activities = \App\Models\ServiceActivity::limit(1)->get();
    echo "   ✓ ServiceActivity model works ({$activities->count()} activities)\n";
} catch (\Exception $e) {
    echo "   ✗ ServiceActivity model ERROR: " . $e->getMessage() . "\n";
}

// Test 7: Test ServiceController::getCategories
echo "\n7. Testing ServiceController::getCategories...\n";
try {
    $controller = new \App\Http\Controllers\Api\ServiceController();
    $response = $controller->getCategories();
    echo "   ✓ ServiceController::getCategories works\n";
    echo "   Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "   ✗ ServiceController::getCategories ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 8: Test ServiceAnalyticsController::getMarketplaceStats
echo "\n8. Testing ServiceAnalyticsController::getMarketplaceStats...\n";
try {
    $controller = new \App\Http\Controllers\Api\ServiceAnalyticsController();
    $response = $controller->getMarketplaceStats();
    echo "   ✓ ServiceAnalyticsController::getMarketplaceStats works\n";
    echo "   Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "   ✗ ServiceAnalyticsController::getMarketplaceStats ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 9: Test ServiceAnalyticsController::getLiveActivityFeed
echo "\n9. Testing ServiceAnalyticsController::getLiveActivityFeed...\n";
try {
    $controller = new \App\Http\Controllers\Api\ServiceAnalyticsController();
    $request = new \Illuminate\Http\Request();
    $response = $controller->getLiveActivityFeed($request);
    echo "   ✓ ServiceAnalyticsController::getLiveActivityFeed works\n";
    echo "   Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "   ✗ ServiceAnalyticsController::getLiveActivityFeed ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 10: Test ServiceAnalyticsController::getTrendingServices
echo "\n10. Testing ServiceAnalyticsController::getTrendingServices...\n";
try {
    $controller = new \App\Http\Controllers\Api\ServiceAnalyticsController();
    $request = new \Illuminate\Http\Request();
    $response = $controller->getTrendingServices($request);
    echo "   ✓ ServiceAnalyticsController::getTrendingServices works\n";
    echo "   Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "   ✗ ServiceAnalyticsController::getTrendingServices ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 11: Check service_activities table structure
echo "\n11. Checking service_activities table structure...\n";
try {
    $columns = \DB::select("DESCRIBE service_activities");
    echo "   ✓ service_activities table has " . count($columns) . " columns:\n";
    foreach ($columns as $column) {
        echo "      - {$column->Field} ({$column->Type})\n";
    }
} catch (\Exception $e) {
    echo "   ✗ service_activities structure ERROR: " . $e->getMessage() . "\n";
}

// Test 12: Check services table structure
echo "\n12. Checking services table structure...\n";
try {
    $columns = \DB::select("DESCRIBE services");
    echo "   ✓ services table has " . count($columns) . " columns:\n";
    foreach ($columns as $column) {
        echo "      - {$column->Field} ({$column->Type})\n";
    }
} catch (\Exception $e) {
    echo "   ✗ services structure ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
