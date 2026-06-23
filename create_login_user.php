<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATING LOGIN USER ===\n\n";

try {
    // Generate unique user_uid
    $user_uid = strtoupper(uniqid());
    
    // Create user with all required fields
    $userData = [
        'user_uid' => $user_uid,
        'first_name' => 'Rizky',
        'last_name' => 'Admin',
        'email' => 'rizky@worldwideadverts.info',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'group_id' => 1, // Admin group if exists
        'is_super_admin' => true, // Make admin
        'can_manage_users' => true,
        'can_manage_categories' => true,
        'can_manage_listings' => true,
        'can_manage_dashboard' => true,
        'can_view_analytics' => true,
        'kyc_status' => 'verified',
        'kyc_verified_at' => now(),
        'email_verified' => true,
        'mobile_verified' => true,
        'created_at' => now(),
        'updated_at' => now()
    ];
    
    $userId = DB::table('users')->insertGetId($userData);
    
    echo "✓ SUCCESS: User created successfully!\n\n";
    echo "LOGIN CREDENTIALS:\n";
    echo "==================\n";
    echo "Email: rizky@worldwideadverts.info\n";
    echo "Password: password123\n";
    echo "User ID: $userId\n";
    echo "User UID: $user_uid\n\n";
    
    echo "ADMIN PRIVILEGES:\n";
    echo "================\n";
    echo "- Super Admin: YES\n";
    echo "- Can Manage Users: YES\n";
    echo "- Can Manage Categories: YES\n";
    echo "- Can Manage Listings: YES\n";
    echo "- Can Manage Dashboard: YES\n";
    echo "- Can View Analytics: YES\n\n";
    
    echo "You can now login at: http://127.0.0.1:8000/login\n";
    echo "Use the credentials above.\n\n";
    
} catch (Exception $e) {
    echo "✗ Failed to create user: " . $e->getMessage() . "\n\n";
    
    // Try a simpler version if the above fails
    echo "Trying simpler user creation...\n";
    try {
        $simpleUserData = [
            'user_uid' => strtoupper(uniqid()),
            'first_name' => 'Rizky',
            'last_name' => 'Admin',
            'email' => 'rizky@worldwideadverts.info',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $userId = DB::table('users')->insertGetId($simpleUserData);
        
        echo "✓ Simple user created successfully!\n";
        echo "Email: rizky@worldwideadverts.info\n";
        echo "Password: password123\n";
        echo "User ID: $userId\n";
        
    } catch (Exception $e2) {
        echo "✗ Simple user creation also failed: " . $e2->getMessage() . "\n";
    }
}

echo "\n=== VERIFICATION ===\n";

$verify_user = DB::table('users')->where('email', 'rizky@worldwideadverts.info')->first();
if ($verify_user) {
    echo "✓ User verified in database:\n";
    echo "  Email: " . $verify_user->email . "\n";
    echo "  Name: " . $verify_user->first_name . " " . $verify_user->last_name . "\n";
    echo "  User UID: " . $verify_user->user_uid . "\n";
    echo "  Super Admin: " . ($verify_user->is_super_admin ? 'YES' : 'NO') . "\n";
    echo "  Email Verified: " . ($verify_user->email_verified_at ? 'YES' : 'NO') . "\n";
    echo "  Created: " . $verify_user->created_at . "\n";
    
    echo "\n✓ READY FOR LOGIN!\n";
} else {
    echo "✗ User creation failed completely\n";
}
