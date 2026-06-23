<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== LOGIN DEBUGGING ===\n\n";

echo "AUTHENTICATION CONFIGURATION:\n";
echo "Default guard: " . config('auth.defaults.guard') . "\n";
echo "Web guard provider: " . config('auth.guards.web.provider') . "\n";
echo "Web guard driver: " . config('auth.guards.web.driver') . "\n";
echo "Customers provider model: " . config('auth.providers.customers.model') . "\n";
echo "Users provider model: " . config('auth.providers.users.model') . "\n\n";

echo "=== CHECKING BOTH TABLES ===\n";

// Check customers table
$customers_exist = DB::getSchemaBuilder()->hasTable('customer');
echo "Customer table: " . ($customers_exist ? "EXISTS" : "MISSING") . "\n";
if ($customers_exist) {
    $customer_count = DB::table('customer')->count();
    echo "Customer records: $customer_count\n";
    
    $customer = DB::table('customer')->where('customer_uid', 'rizky@worldwideadverts.info')->first();
    if ($customer) {
        echo "✓ Found customer with email rizky@worldwideadverts.info\n";
        echo "  Customer ID: " . $customer->customer_id . "\n";
        echo "  Customer UID: " . $customer->customer_uid . "\n";
        echo "  Has password: " . (!empty($customer->password) ? 'YES' : 'NO') . "\n";
    } else {
        echo "✗ No customer found with rizky@worldwideadverts.info\n";
        
        // Show all customers
        $all_customers = DB::table('customer')->select('customer_uid', 'customer_email')->get();
        if ($all_customers->count() > 0) {
            echo "Available customers:\n";
            foreach ($all_customers as $c) {
                echo "  - " . $c->customer_uid . " / " . $c->customer_email . "\n";
            }
        }
    }
}

// Check users table
$users_exist = DB::getSchemaBuilder()->hasTable('users');
echo "\nUsers table: " . ($users_exist ? "EXISTS" : "MISSING") . "\n";
if ($users_exist) {
    $user_count = DB::table('users')->count();
    echo "User records: $user_count\n";
    
    $user = DB::table('users')->where('email', 'rizky@worldwideadverts.info')->first();
    if ($user) {
        echo "✓ Found user with email rizky@worldwideadverts.info\n";
        echo "  User ID: " . $user->user_id . "\n";
        echo "  User UID: " . $user->user_uid . "\n";
        echo "  Has password: " . (!empty($user->password) ? 'YES' : 'NO') . "\n";
        
        // Test password verification
        echo "\n=== PASSWORD TESTING ===\n";
        
        $test_passwords = ['password123', 'admin123', 'admin'];
        foreach ($test_passwords as $pwd) {
            $verified = Hash::check($pwd, $user->password);
            echo "Password '$pwd': " . ($verified ? "✓ MATCHES" : "✗ NO MATCH") . "\n";
        }
    } else {
        echo "✗ No user found with rizky@worldwideadverts.info\n";
    }
}

echo "\n=== SOLUTION ===\n";

if ($customers_exist && DB::table('customer')->count() == 0) {
    echo "The login is trying to authenticate against the 'customer' table,\n";
    echo "but there are no customers. You need to:\n\n";
    
    echo "OPTION 1: Create a customer account (recommended for login)\n";
    echo "OPTION 2: Change the auth guard to use 'users' provider\n";
    echo "OPTION 3: Create a user in the customer table\n\n";
    
    echo "Let's create a customer account...\n";
    
    try {
        // Check customer table structure
        $customer_columns = DB::getSchemaBuilder()->getColumnListing('customer');
        echo "Customer table columns: " . implode(', ', $customer_columns) . "\n\n";
        
        // Create customer
        $customerData = [
            'customer_uid' => strtoupper(uniqid()),
            'customer_email' => 'rizky@worldwideadverts.info',
            'password' => Hash::make('admin123'), // Use the password they're trying
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Add other fields if they exist
        if (in_array('first_name', $customer_columns)) {
            $customerData['first_name'] = 'Rizky';
        }
        if (in_array('last_name', $customer_columns)) {
            $customerData['last_name'] = 'Admin';
        }
        
        $customerId = DB::table('customer')->insertGetId($customerData);
        
        echo "✓ CUSTOMER CREATED SUCCESSFULLY!\n\n";
        echo "LOGIN CREDENTIALS (for customer login):\n";
        echo "Email: rizky@worldwideadverts.info\n";
        echo "Password: admin123\n";
        echo "Customer ID: $customerId\n\n";
        
        echo "Now try logging in with these credentials.\n";
        
    } catch (Exception $e) {
        echo "✗ Failed to create customer: " . $e->getMessage() . "\n";
    }
}
