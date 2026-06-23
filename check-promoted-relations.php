<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Promoted Adverts Relationships ===\n";

try {
    // Check promoted adverts with relationships
    $adverts = \App\Models\PromotedAdvert::with(['category', 'user'])->get();
    echo "Total promoted adverts with relationships: " . $adverts->count() . "\n";
    
    foreach ($adverts as $advert) {
        echo "\nAdvert ID: {$advert->id}\n";
        echo "Title: {$advert->title}\n";
        echo "Category ID: {$advert->category_id}\n";
        echo "Category: " . ($advert->category ? $advert->category->name : 'NULL') . "\n";
        echo "User ID: {$advert->user_id}\n";
        echo "User: " . ($advert->user ? $advert->user->name : 'NULL') . "\n";
    }
    
    // Test the exact query from controller
    echo "\n=== Testing Controller Query ===\n";
    $query = \App\Models\PromotedAdvert::active()->with(['category', 'user']);
    $results = $query->get();
    echo "Controller query results: " . $results->count() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== Done ===\n";
