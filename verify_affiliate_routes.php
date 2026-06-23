<?php

// Backend Route Verification Script
// Run this in Laravel backend to verify affiliate routes are properly registered

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all routes
$routes = app('router')->getRoutes();

echo "🔍 AFFILIATE ROUTES VERIFICATION\n";
echo "================================\n\n";

$affiliateRoutes = [];

foreach ($routes as $route) {
    $uri = $route->uri();
    if (str_contains($uri, 'affiliates')) {
        $methods = implode(', ', $route->methods());
        $action = $route->getAction('uses');
        $controller = class_basename($action[0] ?? 'Unknown');
        $method = $action[1] ?? 'unknown';
        
        $affiliateRoutes[] = [
            'uri' => $uri,
            'methods' => $methods,
            'controller' => $controller,
            'method' => $method
        ];
        
        echo "✅ {$methods} {$uri}\n";
        echo "   → {$controller}@{$method}\n\n";
    }
}

echo "📊 SUMMARY\n";
echo "==========\n";
echo "Total affiliate routes found: " . count($affiliateRoutes) . "\n\n";

$expectedRoutes = [
    'api/affiliates/categories',
    'api/affiliates/business-offers',
    'api/affiliates/business-offers/{id}',
    'api/affiliates/user-posts',
    'api/affiliates/user-posts/{id}',
    'api/affiliates/upsell-plans',
    'api/affiliates/search',
    'api/affiliates/track-click'
];

echo "Expected routes:\n";
foreach ($expectedRoutes as $expected) {
    $found = false;
    foreach ($affiliateRoutes as $route) {
        if ($route['uri'] === $expected) {
            echo "✅ {$expected}\n";
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "❌ {$expected} - NOT FOUND\n";
    }
}

echo "\n🚨 CHECKING FOR V1 ROUTES\n";
echo "========================\n";
$v1Found = false;
foreach ($routes as $route) {
    $uri = $route->uri();
    if (str_contains($uri, 'v1/affiliates')) {
        echo "❌ Found v1 route: {$uri}\n";
        $v1Found = true;
    }
}

if (!$v1Found) {
    echo "✅ No v1/affiliates routes found (good!)\n";
}

echo "\n🎯 FRONTEND API URLS TO TEST\n";
echo "============================\n";
$baseUrl = 'http://localhost:8000/api';
foreach ($expectedRoutes as $route) {
    echo "GET {$baseUrl}/{$route}\n";
}

echo "\n📝 INSTRUCTIONS\n";
echo "==============\n";
echo "1. Run: php artisan route:list --name=affiliates\n";
echo "2. Test: curl -i http://localhost:8000/api/affiliates/categories\n";
echo "3. Clear browser cache completely\n";
echo "4. Restart frontend dev server\n";

?>
