<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LOGIN TROUBLESHOOTING ===\n\n";

// Check users table
echo "Checking users table:\n";
$users_exist = DB::getSchemaBuilder()->hasTable('users');
echo $users_exist ? 'EXISTS' : 'MISSING';

if ($users_exist) {
    echo ' - Records: ' . DB::table('users')->count() . "\n\n";
    
    // Look for the specific user
    $user = DB::table('users')->where('email', 'rizky@worldwideadverts.info')->first();
    if ($user) {
        echo "✓ Found user rizky@worldwideadverts.info\n";
        echo "  ID: " . $user->id . "\n";
        echo "  Email: " . $user->email . "\n";
        echo "  Password hash exists: " . (!empty($user->password) ? 'YES' : 'NO') . "\n";
        echo "  Email verified: " . ($user->email_verified_at ? 'YES' : 'NO') . "\n";
        echo "  Created: " . $user->created_at . "\n";
    } else {
        echo "✗ User rizky@worldwideadverts.info NOT FOUND\n\n";
        
        // Show all users
        $all_users = DB::table('users')->select('email', 'created_at')->get();
        if ($all_users->count() > 0) {
            echo "Available users in database:\n";
            foreach ($all_users as $u) {
                echo "  - " . $u->email . " (created: " . $u->created_at . ")\n";
            }
        } else {
            echo "No users found in database!\n";
        }
    }
} else {
    echo "\n✗ Users table does not exist!\n";
}

echo "\n=== AUTHENTICATION CONFIGURATION ===\n";

// Check auth configuration
echo "Auth model: " . config('auth.providers.users.model', 'App\Models\User') . "\n";
echo "Guard: " . config('auth.defaults.guard', 'web') . "\n";

// Check if User model exists
if (class_exists('App\Models\User')) {
    echo "✓ User model exists\n";
    
    // Check User model table configuration
    $user_model = new \App\Models\User();
    echo "User model table: " . $user_model->getTable() . "\n";
} else {
    echo "✗ User model not found\n";
}

echo "\n=== RECOMMENDATIONS ===\n";

if (!$users_exist) {
    echo "1. Create users table (should exist from migrations)\n";
    echo "2. Run migrations: php artisan migrate\n";
} elseif (DB::table('users')->count() == 0) {
    echo "1. Create a user account\n";
    echo "2. Run UserSeeder or create user manually\n";
} elseif (!DB::table('users')->where('email', 'rizky@worldwideadverts.info')->exists()) {
    echo "1. Create user with email rizky@worldwideadverts.info\n";
    echo "2. Or use existing user credentials\n";
} else {
    echo "1. Check if password is correct\n";
    echo "2. Verify email is verified\n";
    echo "3. Check authentication logs\n";
}

echo "\n=== CREATING TEST USER ===\n";

// Create a test user if needed
if ($users_exist && !DB::table('users')->where('email', 'rizky@worldwideadverts.info')->exists()) {
    try {
        $password = 'password123'; // Default password for testing
        $hashedPassword = Hash::make($password);
        
        $userId = DB::table('users')->insertGetId([
            'name' => 'Rizky Admin',
            'email' => 'rizky@worldwideadverts.info',
            'password' => $hashedPassword,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        echo "✓ Created test user:\n";
        echo "  Email: rizky@worldwideadverts.info\n";
        echo "  Password: password123\n";
        echo "  User ID: " . $userId . "\n";
        echo "\nYou can now login with these credentials.\n";
        
    } catch (Exception $e) {
        echo "✗ Failed to create user: " . $e->getMessage() . "\n";
    }
} else {
    echo "User already exists or table missing.\n";
}
