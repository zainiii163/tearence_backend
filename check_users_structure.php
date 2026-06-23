<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== USERS TABLE STRUCTURE ===\n\n";

// Get table structure
$columns = DB::getSchemaBuilder()->getColumnListing('users');
echo "Users table columns:\n";
foreach ($columns as $column) {
    echo "  - $column\n";
}

echo "\n=== CREATING USER WITH CORRECT STRUCTURE ===\n";

try {
    // Check what columns are available
    $column_list = DB::getSchemaBuilder()->getColumnListing('users');
    
    // Determine what fields to use
    $user_data = [
        'email' => 'rizky@worldwideadverts.info',
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now()
    ];
    
    // Add name field if it exists
    if (in_array('name', $column_list)) {
        $user_data['name'] = 'Rizky Admin';
    }
    
    // Add other common fields if they exist
    if (in_array('first_name', $column_list)) {
        $user_data['first_name'] = 'Rizky';
    }
    if (in_array('last_name', $column_list)) {
        $user_data['last_name'] = 'Admin';
    }
    if (in_array('username', $column_list)) {
        $user_data['username'] = 'rizky';
    }
    
    // Insert the user
    $userId = DB::table('users')->insertGetId($user_data);
    
    echo "✓ Successfully created user:\n";
    echo "  Email: rizky@worldwideadverts.info\n";
    echo "  Password: password123\n";
    echo "  User ID: $userId\n";
    echo "  Fields used: " . implode(', ', array_keys($user_data)) . "\n";
    
    echo "\nYou can now login with:\n";
    echo "  Email: rizky@worldwideadverts.info\n";
    echo "  Password: password123\n";
    
} catch (Exception $e) {
    echo "✗ Failed to create user: " . $e->getMessage() . "\n";
    
    echo "\nLet's check the actual migration file to see the expected structure:\n";
    
    // Find the users migration file
    $migration_files = glob(database_path('migrations') . '/*create_users_table.php');
    if (!empty($migration_files)) {
        $migration_file = $migration_files[0];
        echo "Found migration: " . basename($migration_file) . "\n";
        
        // Read and show the structure
        $content = file_get_contents($migration_file);
        if (preg_match('/Schema::create\(\'users\'[^}]+}/s', $content, $matches)) {
            echo "Migration structure:\n";
            echo $matches[0] . "\n";
        }
    }
}

echo "\n=== VERIFYING USER CREATION ===\n";

$verify_user = DB::table('users')->where('email', 'rizky@worldwideadverts.info')->first();
if ($verify_user) {
    echo "✓ User successfully created and verified\n";
    echo "  Email: " . $verify_user->email . "\n";
    echo "  Created: " . $verify_user->created_at . "\n";
} else {
    echo "✗ User creation failed\n";
}
