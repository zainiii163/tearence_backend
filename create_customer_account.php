<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREATING CUSTOMER ACCOUNT FOR LOGIN ===\n\n";

try {
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
    } else {
        // Create new customer
        $customerData = [
            'customer_uid' => strtoupper(uniqid()),
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
    echo "The login system uses the 'customer' table (configured in auth.php)\n";
    echo "You should now be able to login successfully.\n";
    
    // Verify the customer was created
    $verify_customer = DB::table('customer')->where('email', 'rizky@worldwideadverts.info')->first();
    if ($verify_customer) {
        echo "\n✓ VERIFICATION SUCCESSFUL:\n";
        echo "  Email: " . $verify_customer->email . "\n";
        echo "  Name: " . $verify_customer->first_name . " " . $verify_customer->last_name . "\n";
        echo "  Customer UID: " . $verify_customer->customer_uid . "\n";
        echo "  Email Verified: " . ($verify_customer->email_verified_at ? 'YES' : 'NO') . "\n";
        echo "  Created: " . $verify_customer->created_at . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ Failed to create/update customer: " . $e->getMessage() . "\n";
}

echo "\n=== AUTHENTICATION CONFIGURATION REMINDER ===\n";
echo "Default guard: web\n";
echo "Web guard provider: customers\n";
echo "This means login uses the Customer model and customer table\n\n";

echo "=== REGISTRATION FIX ===\n";
echo "Fixed RegisteredUserController validation:\n";
echo "- Changed 'unique:user' to 'unique:users'\n";
echo "- Registration should now work correctly\n\n";

echo "Try both login and registration now!\n";
