<?php

echo "Testing API route registration...\n";

// Test if we can add a simple test route to verify API routes are working
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n" .
                    "Accept: application/json\r\n"
    ]
]);

// Test a route that should exist - let's try a common Laravel route
echo "1. Testing /api/health (common health check)\n";
$response = @file_get_contents('http://127.0.0.1:8000/api/health', false, $context);
if ($response !== false) {
    echo "✓ Health route working\n";
} else {
    echo "✗ Health route not found (expected)\n";
}

// Let's check what routes are actually registered by testing the route:list command
echo "\n2. Checking if affiliate routes are registered\n";
// We'll use curl to get the route list
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/test-affiliate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($response !== false && $httpCode === 200) {
    echo "✓ Test route working\n";
} else {
    echo "✗ Test route not found (HTTP $httpCode)\n";
    if ($error) {
        echo "  Error: $error\n";
    }
}

echo "\n3. Testing direct route check\n";
// Let's try to access the Laravel route directly through a web request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/affiliates/categories');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Headers: " . substr($headers, 0, 200) . "...\n";
echo "Body: " . substr($body, 0, 200) . "...\n";

if ($error) {
    echo "Error: $error\n";
}

echo "\nRoute testing complete.\n";
