<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "Testing Job Seeker Profile Creation\n";
echo "====================================\n\n";

// Get a test user ID
$user = DB::table('users')->first();
if (!$user) {
    echo "ERROR: No users found in database. Please create a user first.\n";
    exit(1);
}

$userId = isset($user->id) ? $user->id : (isset($user->user_id) ? $user->user_id : (isset($user->ID) ? $user->ID : 2));
$email = isset($user->email) ? $user->email : 'N/A';
echo "Using test user ID: {$userId}\n";
echo "User email: {$email}\n\n";

// Check if user already has a job seeker profile
$existingProfile = DB::table('job_seekers')->where('user_id', $userId)->first();
if ($existingProfile) {
    echo "User already has a job seeker profile with ID: {$existingProfile->id}\n";
    echo "Deleting existing profile...\n";
    DB::table('job_seekers')->where('user_id', $userId)->delete();
    echo "Existing profile deleted.\n\n";
}

// Create a new job seeker profile
echo "Creating new job seeker profile...\n";
$seeker = DB::table('job_seekers')->insert([
    'user_id' => $userId,
    'country' => 'USA',
    'city' => 'New York',
    'is_active' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

if ($seeker) {
    echo "✓ Job seeker profile created successfully!\n";
    
    // Retrieve the created profile
    $profile = DB::table('job_seekers')->where('user_id', $userId)->first();
    echo "Profile ID: {$profile->id}\n";
    echo "Country: {$profile->country}\n";
    echo "City: {$profile->city}\n";
    echo "Is Active: " . ($profile->is_active ? 'Yes' : 'No') . "\n";
    echo "Created At: {$profile->created_at}\n";
    echo "\n✓ Test PASSED: Job seeker profile can be created successfully.\n";
    echo "\nYou can now try creating a profile from the frontend form.\n";
} else {
    echo "✗ ERROR: Failed to create job seeker profile.\n";
    exit(1);
}
