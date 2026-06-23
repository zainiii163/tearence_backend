<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PromotedAdvert;

echo "=== Checking Recent Promoted Adverts ===\n\n";

// Check all adverts ordered by creation date
$recentAdverts = PromotedAdvert::select('id', 'title', 'status', 'is_active', 'created_at', 'user_id')
    ->orderBy('created_at', 'desc')
    ->get();

echo "All adverts ordered by creation date (newest first):\n";
foreach ($recentAdverts as $index => $advert) {
    $date = new \DateTime($advert->created_at);
    $formattedDate = $date->format('Y-m-d H:i:s');
    echo "#" . ($index + 1) . " ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}, Active: " . ($advert->is_active ? 'Yes' : 'No') . ", User ID: " . ($advert->user_id ?: 'NULL') . ", Created: {$formattedDate}\n";
}

echo "\n=== Adverts created in last hour ===\n";
$oneHourAgo = (new \DateTime())->sub(new \DateInterval('PT1H'));
$veryRecentAdverts = PromotedAdvert::where('created_at', '>=', $oneHourAgo)
    ->select('id', 'title', 'status', 'is_active', 'created_at', 'user_id')
    ->orderBy('created_at', 'desc')
    ->get();

if ($veryRecentAdverts->isEmpty()) {
    echo "No adverts created in the last hour.\n";
} else {
    echo "Adverts created in last hour: " . $veryRecentAdverts->count() . "\n";
    foreach ($veryRecentAdverts as $advert) {
        $date = new \DateTime($advert->created_at);
        $formattedDate = $date->format('Y-m-d H:i:s');
        echo "ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}, Created: {$formattedDate}\n";
    }
}

echo "\n=== Adverts without user_id ===\n";
$advertsWithoutUser = PromotedAdvert::whereNull('user_id')->get();

if ($advertsWithoutUser->isEmpty()) {
    echo "No adverts without user_id found.\n";
} else {
    echo "Adverts without user_id: " . $advertsWithoutUser->count() . "\n";
    foreach ($advertsWithoutUser as $advert) {
        echo "ID: {$advert->id}, Title: {$advert->title}, Status: {$advert->status}\n";
    }
}

echo "\n=== Done ===\n";
