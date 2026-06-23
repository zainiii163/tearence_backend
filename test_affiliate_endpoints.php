<?php

require_once 'vendor/autoload.php';

// Test Affiliate API Endpoints
echo "=== Testing Affiliate API Endpoints ===\n\n";

$baseUrl = 'http://localhost:8000/api';

// Test 1: Categories
echo "1. Testing GET /api/affiliates/categories\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n"
    ]
]);

$response = @file_get_contents($baseUrl . '/affiliates/categories', false, $context);
if ($response) {
    $data = json_decode($response, true);
    echo "✓ Categories endpoint working\n";
    echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'count' => count($data['data'] ?? [])]) . "\n\n";
} else {
    echo "✗ Categories endpoint failed\n\n";
}

// Test 2: Business Offers
echo "2. Testing GET /api/affiliates/business-offers\n";
$response = @file_get_contents($baseUrl . '/affiliates/business-offers', false, $context);
if ($response) {
    $data = json_decode($response, true);
    echo "✓ Business offers endpoint working\n";
    echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'total' => $data['data']['total'] ?? 0]) . "\n\n";
} else {
    echo "✗ Business offers endpoint failed\n\n";
}

// Test 3: User Posts
echo "3. Testing GET /api/affiliates/user-posts\n";
$response = @file_get_contents($baseUrl . '/affiliates/user-posts', false, $context);
if ($response) {
    $data = json_decode($response, true);
    echo "✓ User posts endpoint working\n";
    echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'total' => $data['data']['total'] ?? 0]) . "\n\n";
} else {
    echo "✗ User posts endpoint failed\n\n";
}

// Test 4: Upsell Plans
echo "4. Testing GET /api/affiliates/upsell-plans\n";
$response = @file_get_contents($baseUrl . '/affiliates/upsell-plans', false, $context);
if ($response) {
    $data = json_decode($response, true);
    echo "✓ Upsell plans endpoint working\n";
    echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'count' => count($data['data'] ?? [])]) . "\n\n";
} else {
    echo "✗ Upsell plans endpoint failed\n\n";
}

// Test 5: Search
echo "5. Testing GET /api/affiliates/search\n";
$response = @file_get_contents($baseUrl . '/affiliates/search?q=test&type=all', false, $context);
if ($response) {
    $data = json_decode($response, true);
    echo "✓ Search endpoint working\n";
    echo "Response: " . json_encode(['success' => $data['success'] ?? false]) . "\n\n";
} else {
    echo "✗ Search endpoint failed\n\n";
}

echo "=== Affiliate API Test Complete ===\n";
