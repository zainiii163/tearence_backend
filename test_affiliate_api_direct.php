<?php

// Test affiliate API directly from backend
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

echo "=== Testing Affiliate API Directly ===\n";

// Test 1: Categories endpoint
echo "1. Testing /api/v1/affiliates/categories\n";
try {
    $response = Http::get('http://127.0.0.1:8000/api/v1/affiliates/categories');
    $data = $response->json();
    
    if ($response->successful() && $data['success'] ?? false) {
        echo "✓ Categories working - " . count($data['data'] ?? 0) . " categories\n";
    } else {
        echo "✗ Categories failed\n";
        echo "  Status: " . $response->status() . "\n";
    }
} catch (Exception $e) {
    echo "✗ Categories error: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Check if Laravel is trying to render view
echo "2. Testing route registration\n";
$routes = app('router')->getRoutes();

$affiliateRoutes = [];
foreach ($routes as $route) {
    if (str_contains($route->uri(), 'affiliates')) {
        $affiliateRoutes[] = $route->uri() . ' -> ' . $route->getActionMethod();
    }
}

if (!empty($affiliateRoutes)) {
    echo "Found affiliate routes:\n";
    foreach ($affiliateRoutes as $route) {
        echo "  - " . $route . "\n";
    }
} else {
    echo "No affiliate routes found\n";
}

echo "\n=== API Test Complete ===\n";
