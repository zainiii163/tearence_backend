<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Promoted Adverts Data ===\n";

try {
    // Check all promoted adverts
    $allAdverts = \App\Models\PromotedAdvert::all();
    echo "Total promoted adverts: " . $allAdverts->count() . "\n";
    
    foreach ($allAdverts as $advert) {
        echo "ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}, Active: " . ($advert->is_active ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n=== Active Promoted Adverts ===\n";
    $activeAdverts = \App\Models\PromotedAdvert::active()->get();
    echo "Active promoted adverts: " . $activeAdverts->count() . "\n";
    
    if ($activeAdverts->count() > 0) {
        foreach ($activeAdverts as $advert) {
            echo "ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Done ===\n";
