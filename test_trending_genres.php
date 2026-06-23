<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

try {
    echo "Testing trending genres endpoint...\n";
    
    // Test the trending genres endpoint
    $response = \Illuminate\Support\Facades\Route::dispatch(
        \Illuminate\Http\Request::create('/api/v1/books-adverts/trending-genres', 'GET')
    );
    
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Response: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
