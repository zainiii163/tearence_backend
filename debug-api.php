<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Debugging Promoted Adverts API ===\n";

try {
    // Test basic query
    echo "1. Testing basic query...\n";
    $adverts = \App\Models\PromotedAdvert::active()->get();
    echo "Found {$adverts->count()} active adverts\n";
    
    // Test with category relationship
    echo "\n2. Testing with category relationship...\n";
    $advertsWithCategory = \App\Models\PromotedAdvert::active()->with('category')->get();
    echo "Found {$advertsWithCategory->count()} adverts with categories\n";
    
    // Test with user relationship
    echo "\n3. Testing with user relationship...\n";
    $advertsWithUser = \App\Models\PromotedAdvert::active()->with('user')->get();
    echo "Found {$advertsWithUser->count()} adverts with users\n";
    
    // Test with both relationships
    echo "\n4. Testing with both relationships...\n";
    $advertsWithBoth = \App\Models\PromotedAdvert::active()->with(['category', 'user'])->get();
    echo "Found {$advertsWithBoth->count()} adverts with both relationships\n";
    
    // Test pagination
    echo "\n5. Testing pagination...\n";
    $paginated = \App\Models\PromotedAdvert::active()->with(['category', 'user'])->paginate(12);
    echo "Paginated result: {$paginated->total()} total, {$paginated->count()} on current page\n";
    
    // Test the exact controller logic
    echo "\n6. Testing controller logic...\n";
    $query = \App\Models\PromotedAdvert::active()->with(['category', 'user']);
    $results = $query->paginate(12);
    
    echo "Controller query successful!\n";
    echo "Total results: {$results->total()}\n";
    echo "Current page: {$results->currentPage()}\n";
    echo "Per page: {$results->perPage()}\n";
    
    // Format one result
    if ($results->count() > 0) {
        $firstAdvert = $results->first();
        echo "\nFirst advert data:\n";
        echo "ID: {$firstAdvert->id}\n";
        echo "Title: {$firstAdvert->title}\n";
        echo "Category: " . ($firstAdvert->category ? $firstAdvert->category->name : 'NULL') . "\n";
        echo "User: " . ($firstAdvert->user ? $firstAdvert->user->name : 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Done ===\n";
