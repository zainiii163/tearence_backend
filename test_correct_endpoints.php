<?php

echo "Testing correct API endpoints with v1 prefix...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
    ]
]);

// Test the test route with v1 prefix
echo "1. Testing /api/v1/test-api\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/v1/test-api', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Test API route working with v1 prefix\n";
        echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'message' => $data['message'] ?? '']) . "\n";
    } else {
        echo "✗ Test API route returned invalid JSON\n";
        echo "Raw response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "✗ Test API route failed with v1 prefix\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\n2. Testing /api/v1/affiliates/categories\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/v1/affiliates/categories', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Categories API route working with v1 prefix\n";
        echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'count' => count($data['data'] ?? [])]) . "\n";
    } else {
        echo "✗ Categories API route returned invalid JSON\n";
    }
} else {
    echo "✗ Categories API route failed with v1 prefix\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\n3. Testing /api/v1/affiliates/business-offers\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/v1/affiliates/business-offers', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Business offers API route working with v1 prefix\n";
        echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'total' => $data['data']['total'] ?? 0]) . "\n";
    } else {
        echo "✗ Business offers API route returned invalid JSON\n";
    }
} else {
    echo "✗ Business offers API route failed with v1 prefix\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\n4. Testing /api/v1/affiliates/user-posts\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/v1/affiliates/user-posts', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ User posts API route working with v1 prefix\n";
        echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'total' => $data['data']['total'] ?? 0]) . "\n";
    } else {
        echo "✗ User posts API route returned invalid JSON\n";
    }
} else {
    echo "✗ User posts API route failed with v1 prefix\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\nTest complete.\n";
