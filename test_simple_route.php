<?php

echo "Testing simple API routes...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
    ]
]);

// Test a basic API route
echo "1. Testing /api/test (basic route)\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/test', false, $context);
if ($response !== false) {
    echo "✓ Basic API route working\n";
} else {
    echo "✗ Basic API route failed\n";
}

// Test another route that might exist
echo "2. Testing /api/users (common route)\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/users', false, $context);
if ($response !== false) {
    echo "✓ Users route working\n";
} else {
    echo "✗ Users route failed\n";
}

// Test the route with debug info
echo "3. Testing affiliate route with debug\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/affiliates/categories', false, $context);
if ($response !== false) {
    echo "✓ Affiliate route working\n";
    echo "Response: " . substr($response, 0, 100) . "...\n";
} else {
    echo "✗ Affiliate route failed\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

echo "Route testing complete.\n";
