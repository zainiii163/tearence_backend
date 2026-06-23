<?php
/**
 * Laravel Optimization Script
 * This script helps optimize Laravel performance and fix common issues
 */

echo "🚀 Optimizing Laravel Backend...\n\n";

// Clear all caches
echo "🧹 Clearing caches...\n";
$commands = [
    'php artisan cache:clear',
    'php artisan config:clear', 
    'php artisan route:clear',
    'php artisan view:clear',
    'php artisan config:cache',
    'php artisan route:cache',
    'php artisan event:cache',
];

foreach ($commands as $command) {
    echo "Running: $command\n";
    passthru($command);
    echo "\n";
}

// Optimize autoloader
echo "📦 Optimizing autoloader...\n";
passthru('composer dump-autoload --optimize');

// Check for common issues
echo "\n🔍 Checking for common issues...\n";

// Check if storage is writable
$storagePath = __DIR__ . '/storage';
if (is_writable($storagePath)) {
    echo "✅ Storage directory is writable\n";
} else {
    echo "❌ Storage directory is not writable\n";
}

// Check if .env file exists
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo "✅ .env file exists\n";
} else {
    echo "❌ .env file missing\n";
    echo "Creating .env from example...\n";
    copy(__DIR__ . '/.env.example', $envPath);
    echo "Please update your .env file with proper database credentials\n";
}

// Check app key
echo "🔑 Generating application key...\n";
passthru('php artisan key:generate --force');

echo "\n✅ Laravel optimization complete!\n";
echo "🚀 Try running: php artisan serve\n";
echo "📝 If issues persist, check:\n";
echo "   - Database connection in .env\n";
echo "   - PHP version compatibility (>= 8.1)\n";
echo "   - Required PHP extensions: mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, fileinfo\n";
