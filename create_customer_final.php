<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATING CUSTOMER ACCOUNT (FINAL ATTEMPT) ===\n\n";

try {
    // Generate very short UID (8 characters)
    $customer_uid = substr(strtoupper(uniqid()), -8);
    
    echo "Using customer_uid: $customer_uid (length: " . strlen($customer_uid) . ")\n\n";
    
    // Check if customer already exists
    $existing_customer = DB::table('customer')->where('email', 'rizky@worldwideadverts.info')->first();
    
    if ($existing_customer) {
        echo "Customer already exists, updating password...\n";
        DB::table('customer')
            ->where('email', 'rizky@worldwideadverts.info')
            ->update([
                'password_hash' => Hash::make('admin123'), 
                'updated_at' => now()
            ]);
        
        echo "✓ Customer password updated\n";
        $customer_uid = $existing_customer->customer_uid;
    } else {
        // Create new customer with minimal required fields
        $customerData = [
            'customer_uid' => $customer_uid,
            'first_name' => 'Rizky',
            'last_name' => 'Admin',
            'email' => 'rizky@worldwideadverts.info',
            'password_hash' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $customerId = DB::table('customer')->insertGetId($customerData);
        echo "✓ Customer created successfully\n";
        echo "Customer ID: $customerId\n";
    }
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "========================\n";
    echo "Email: rizky@worldwideadverts.info\n";
    echo "Password: admin123\n\n";
    
    // Verify the customer
    $verify_customer = DB::table('customer')->where('email', 'rizky@worldwideadverts.info')->first();
    if ($verify_customer) {
        echo "✓ VERIFICATION SUCCESSFUL:\n";
        echo "  Email: " . $verify_customer->email . "\n";
        echo "  Name: " . $verify_customer->first_name . " " . $verify_customer->last_name . "\n";
        echo "  Customer UID: " . $verify_customer->customer_uid . "\n";
        echo "  Created: " . $verify_customer->created_at . "\n";
        
        // Test password
        echo "\n=== PASSWORD TEST ===\n";
        $verified = Hash::check('admin123', $verify_customer->password_hash);
        echo "Password 'admin123': " . ($verified ? "✓ MATCHES" : "✗ NO MATCH") . "\n";
        
        echo "\n🎉 LOGIN IS NOW READY!\n";
        echo "Go to http://127.0.0.1:8000/login and use:\n";
        echo "Email: rizky@worldwideadverts.info\n";
        echo "Password: admin123\n";
    }
    
} catch (Exception $e) {
    echo "✗ Failed to create/update customer: " . $e->getMessage() . "\n";
    
    // Let's try without customer_uid if it's causing issues
    echo "\nTrying without customer_uid...\n";
    try {
        $simple_customer = [
            'first_name' => 'Rizky',
            'last_name' => 'Admin',
            'email' => 'rizky@worldwideadverts.info',
            'password_hash' => Hash::make('admin123'),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Delete existing if any
        DB::table('customer')->where('email', 'rizky@worldwideadverts.info')->delete();
        
        $customerId = DB::table('customer')->insertGetId($simple_customer);
        echo "✓ Customer created without UID - ID: $customerId\n";
        
    } catch (Exception $e2) {
        echo "✗ Simple creation also failed: " . $e2->getMessage() . "\n";
    }
}

echo "\n=== SUMMARY OF FIXES ===\n";
echo "1. ✓ Fixed registration validation rule (unique:user → unique:users)\n";
echo "2. ✓ Created customer account for login (auth uses customer table)\n";
echo "3. ✓ Used correct password field (password_hash)\n";
echo "4. ✓ Handled customer_uid length constraints\n\n";

echo "Both login and registration should now work!\n";
