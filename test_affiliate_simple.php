<?php

// Simple test to check if affiliate endpoints are working
echo "Testing Affiliate API Endpoints...\n";

// Test using file_get_contents with proper context
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
    ]
]);

// Test categories endpoint
echo "1. Testing /api/affiliates/categories\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/affiliates/categories', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Categories endpoint working - Success: " . ($data['success'] ?? 'false') . "\n";
        if (isset($data['data']) && is_array($data['data'])) {
            echo "  - Found " . count($data['data']) . " categories\n";
        }
    } else {
        echo "✗ Categories endpoint returned invalid JSON\n";
        echo "  Raw response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "✗ Categories endpoint failed to respond\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\n2. Testing /api/affiliates/business-offers\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/affiliates/business-offers', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Business offers endpoint working - Success: " . ($data['success'] ?? 'false') . "\n";
        if (isset($data['data']['total'])) {
            echo "  - Total offers: " . $data['data']['total'] . "\n";
        }
    } else {
        echo "✗ Business offers endpoint returned invalid JSON\n";
    }
} else {
    echo "✗ Business offers endpoint failed to respond\n";
}

echo "\n3. Testing /api/affiliates/user-posts\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/affiliates/user-posts', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ User posts endpoint working - Success: " . ($data['success'] ?? 'false') . "\n";
        if (isset($data['data']['total'])) {
            echo "  - Total posts: " . $data['data']['total'] . "\n";
        }
    } else {
        echo "✗ User posts endpoint returned invalid JSON\n";
    }
} else {
    echo "✗ User posts endpoint failed to respond\n";
}

echo "\nTest complete.\n";
