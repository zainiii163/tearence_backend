<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PromotedAdvert;

echo "=== Checking Pending Promoted Adverts ===\n\n";

// Check all adverts with their status
$allAdverts = PromotedAdvert::select('id', 'title', 'status', 'is_active', 'created_at')->get();

echo "All adverts in database:\n";
foreach ($allAdverts as $advert) {
    echo "ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}, Active: " . ($advert->is_active ? 'Yes' : 'No') . "\n";
}

echo "\n=== Pending Adverts ===\n";
$pendingAdverts = PromotedAdvert::where('status', 'pending')->get();

if ($pendingAdverts->isEmpty()) {
    echo "No pending adverts found.\n";
} else {
    echo "Pending adverts: " . $pendingAdverts->count() . "\n";
    foreach ($pendingAdverts as $advert) {
        echo "ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}\n";
    }
}

echo "\n=== Inactive Adverts ===\n";
$inactiveAdverts = PromotedAdvert::where('is_active', false)->get();

if ($inactiveAdverts->isEmpty()) {
    echo "No inactive adverts found.\n";
} else {
    echo "Inactive adverts: " . $inactiveAdverts->count() . "\n";
    foreach ($inactiveAdverts as $advert) {
        echo "ID: {$advert->id}, Title: {$advert->title}, Active: " . ($advert->is_active ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== Done ===\n";
