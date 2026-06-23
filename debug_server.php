<?php

echo "Debugging Laravel server...\n";

// Test if server is running at all
echo "1. Testing basic server response\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = @file_get_contents('http://127.0.0.1:8000/', false, $context);
if ($response !== false) {
    echo "✓ Server is responding\n";
    echo "Response length: " . strlen($response) . " characters\n";
    echo "First 200 chars: " . substr($response, 0, 200) . "...\n";
} else {
    echo "✗ Server is not responding\n";
    $error = error_get_last();
    if ($error) {
        echo "  Error: " . $error['message'] . "\n";
    }
}

// Test with curl if available
echo "\n2. Testing with curl (if available)\n";
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/affiliates/categories');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response !== false) {
        echo "✓ Curl request successful\n";
        echo "HTTP Code: $httpCode\n";
        echo "Response: " . substr($response, 0, 200) . "...\n";
    } else {
        echo "✗ Curl request failed: $error\n";
    }
} else {
    echo "Curl not available\n";
}

echo "\nDebug complete.\n";
