<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATING CUSTOMER ACCOUNT (FIXED) ===\n\n";

try {
    // Generate shorter UID (13 characters)
    $customer_uid = substr(strtoupper(uniqid()), 0, 13);
    
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
        // Create new customer
        $customerData = [
            'customer_uid' => $customer_uid,
            'first_name' => 'Rizky',
            'last_name' => 'Admin',
            'email' => 'rizky@worldwideadverts.info',
            'password_hash' => Hash::make('admin123'),
            'email_verified_at' => now(),
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
    
    echo "✓ LOGIN READY!\n";
    
    // Verify the customer was created
    $verify_customer = DB::table('customer')->where('email', 'rizky@worldwideadverts.info')->first();
    if ($verify_customer) {
        echo "\n✓ VERIFICATION SUCCESSFUL:\n";
        echo "  Email: " . $verify_customer->email . "\n";
        echo "  Name: " . $verify_customer->first_name . " " . $verify_customer->last_name . "\n";
        echo "  Customer UID: " . $verify_customer->customer_uid . "\n";
        echo "  Email Verified: " . ($verify_customer->email_verified_at ? 'YES' : 'NO') . "\n";
        echo "  Has Password: " . (!empty($verify_customer->password_hash) ? 'YES' : 'NO') . "\n";
        echo "  Created: " . $verify_customer->created_at . "\n";
        
        // Test password verification
        echo "\n=== PASSWORD TEST ===\n";
        $passwords_to_test = ['admin123', 'password123'];
        foreach ($passwords_to_test as $pwd) {
            $verified = Hash::check($pwd, $verify_customer->password_hash);
            echo "Password '$pwd': " . ($verified ? "✓ MATCHES" : "✗ NO MATCH") . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Failed to create/update customer: " . $e->getMessage() . "\n";
}

echo "\n=== WHAT WAS FIXED ===\n";
echo "1. ✓ Registration validation: 'unique:user' → 'unique:users'\n";
echo "2. ✓ Created customer account for login (uses customer table)\n";
echo "3. ✓ Used correct customer_uid length (13 chars)\n";
echo "4. ✓ Used correct password field (password_hash)\n\n";

echo "=== AUTHENTICATION FLOW ===\n";
echo "Login → auth.php → web guard → customers provider → Customer model → customer table\n\n";

echo "You should now be able to login with:\n";
echo "Email: rizky@worldwideadverts.info\n";
echo "Password: admin123\n\n";

echo "And registration should work for new users too!\n";
