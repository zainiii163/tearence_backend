<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

// Check if admin user exists
$admin = \App\Models\User::where('email', 'rizky@worldwideadverts.info')->first();

if ($admin) {
    echo "Admin user found:\n";
    echo "ID: " . $admin->id . "\n";
    echo "Name: " . $admin->name . "\n";
    echo "Email: " . $admin->email . "\n";
    echo "Has password: " . (!empty($admin->password) ? "Yes" : "No") . "\n";
    
    // Test password verification with common passwords
    $testPasswords = ['password', '123456', 'admin', 'admin123', 'rizky123', 'worldwide'];
    
    echo "\nTesting password verification:\n";
    foreach ($testPasswords as $testPass) {
        if (\Illuminate\Support\Facades\Hash::check($testPass, $admin->password)) {
            echo "✓ Password matches: " . $testPass . "\n";
        }
    }
} else {
    echo "Admin user not found with email: rizky@worldwideadverts.info\n";
    
    // List all admin users
$allAdmins = \App\Models\User::take(10)->get();
echo "\nAll admin users in database:\n";
if ($allAdmins->count() > 0) {
    foreach ($allAdmins as $a) {
        echo "- " . $a->email . " (ID: " . $a->id . ", Name: " . $a->name . ")\n";
    }
} else {
    echo "No admin users found in database.\n";
    
    echo "\nCreating admin user 'rizky@worldwideadverts.info' with password 'admin123'...\n";
    
    $admin = new \App\Models\User();
    $admin->user_uid = uniqid();
    $admin->first_name = 'Rizky';
    $admin->last_name = 'Admin';
    $admin->email = 'rizky@worldwideadverts.info';
    $admin->password = \Illuminate\Support\Facades\Hash::make('admin123');
    $admin->email_verified_at = now();
    $admin->is_super_admin = true;
    $admin->can_manage_users = true;
    $admin->can_manage_categories = true;
    $admin->can_manage_listings = true;
    $admin->can_manage_dashboard = true;
    $admin->can_view_analytics = true;
    $admin->save();
    
    echo "Admin user created successfully!\n";
    echo "Email: rizky@worldwideadverts.info\n";
    echo "Password: admin123\n";
}
}
