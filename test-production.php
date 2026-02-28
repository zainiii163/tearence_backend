<?php

/**
 * Production Readiness Test Script
 * Run this script to verify your production setup
 */

echo "🚀 WWA API Production Readiness Test\n";
echo "=====================================\n\n";

// Test 1: Environment
echo "1. Environment Check:\n";
echo "   APP_ENV: " . env('APP_ENV') . "\n";
echo "   APP_DEBUG: " . (env('APP_DEBUG') ? 'true (❌ Should be false)' : 'false (✅)') . "\n";
echo "   APP_URL: " . env('APP_URL') . "\n\n";

// Test 2: Database Connection
echo "2. Database Connection:\n";
try {
    \DB::connection()->getPdo();
    echo "   ✅ Database connection successful\n";
    echo "   Database: " . \DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Cache Connection
echo "3. Cache Connection:\n";
try {
    \Cache::put('test_key', 'test_value', 60);
    $value = \Cache::get('test_key');
    if ($value === 'test_value') {
        echo "   ✅ Cache working\n";
        \Cache::forget('test_key');
    } else {
        echo "   ❌ Cache not working\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Cache connection failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: JWT Configuration
echo "4. JWT Configuration:\n";
$jwtSecret = env('JWT_SECRET');
if (empty($jwtSecret)) {
    echo "   ❌ JWT_SECRET not set\n";
} elseif (strlen($jwtSecret) < 32) {
    echo "   ❌ JWT_SECRET too short (minimum 32 characters)\n";
} else {
    echo "   ✅ JWT_SECRET configured\n";
}
echo "\n";

// Test 5: File Permissions
echo "5. File Permissions:\n";
$paths = ['storage', 'bootstrap/cache'];
foreach ($paths as $path) {
    if (is_writable($path)) {
        echo "   ✅ $path is writable\n";
    } else {
        echo "   ❌ $path is not writable\n";
    }
}
echo "\n";

// Test 6: SSL Certificate
echo "6. SSL Certificate:\n";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    echo "   ✅ HTTPS enabled\n";
} else {
    echo "   ❌ HTTPS not detected (running on HTTP)\n";
}
echo "\n";

// Test 7: API Endpoints
echo "7. API Endpoint Test:\n";
$endpoints = [
    '/api/v1/auth/debug',
    '/api/v1/category',
];

foreach ($endpoints as $endpoint) {
    try {
        $response = file_get_contents(env('APP_URL') . $endpoint);
        if ($response) {
            echo "   ✅ $endpoint accessible\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ $endpoint failed: " . $e->getMessage() . "\n";
    }
}
echo "\n";

// Test 8: Security Headers
echo "8. Security Headers Check:\n";
$requiredHeaders = [
    'X-Frame-Options',
    'X-XSS-Protection',
    'X-Content-Type-Options',
];

foreach ($requiredHeaders as $header) {
    if (function_exists('apache_response_headers')) {
        $headers = apache_response_headers();
        if (isset($headers[$header])) {
            echo "   ✅ $header present\n";
        } else {
            echo "   ⚠️  $header not set (configure in Nginx)\n";
        }
    } else {
        echo "   ⚠️  Cannot check headers (Apache not running)\n";
    }
}
echo "\n";

echo "=====================================\n";
echo "✅ Production readiness test complete!\n";
echo "📝 Review any ❌ items above before going live\n";
echo "🌐 Your API will be available at: " . env('APP_URL') . "\n";
