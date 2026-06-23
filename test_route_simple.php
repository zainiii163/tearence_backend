<?php

echo "Testing simple API route...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
    ]
]);

// Test the new test route
echo "1. Testing /api/test-api\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/test-api', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Test API route working\n";
        echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'message' => $data['message'] ?? '']) . "\n";
    } else {
        echo "✗ Test API route returned invalid JSON\n";
        echo "Raw response: " . substr($response, 0, 200) . "...\n";
    }
} else {
    echo "✗ Test API route failed\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\n2. Testing /api/affiliates/categories\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/affiliates/categories', false, $context);
if ($response !== false) {
    $data = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✓ Categories API route working\n";
        echo "Response: " . json_encode(['success' => $data['success'] ?? false, 'count' => count($data['data'] ?? [])]) . "\n";
    } else {
        echo "✗ Categories API route returned invalid JSON\n";
    }
} else {
    echo "✗ Categories API route failed\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "\nTest complete.\n";
