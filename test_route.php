<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Test if the route exists
try {
    $route = \Illuminate\Support\Facades\Route::getRoutes()->matchRequest(
        \Illuminate\Http\Request::create('/api/v1/store/1/detail', 'GET')
    );
    
    if ($route) {
        echo "Route found: " . $route->getActionName() . "\n";
        echo "Controller: " . $route->getAction('uses') . "\n";
    } else {
        echo "Route not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
