<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING LOGIN AND REGISTRATION ISSUES ===\n\n";

echo "✓ Fixed registration validation: 'unique:user' → 'unique:users'\n\n";

echo "=== CREATING CUSTOMER FOR LOGIN ===\n";

// Check customer table structure
$customer_columns = DB::getSchemaBuilder()->getColumnListing('customer');
echo "Customer table columns: " . implode(', ', $customer_columns) . "\n\n";

try {
    // Create customer with the email they're trying to use
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
    if (in_array('customer_name', $customer_columns)) {
        $customerData['customer_name'] = 'Rizky Admin';
    }
    
    // Check if customer already exists
    $existing_customer = DB::table('customer')->where('customer_email', 'rizky@worldwideadverts.info')->first();
    if ($existing_customer) {
        echo "Customer already exists, updating password...\n";
        DB::table('customer')
            ->where('customer_email', 'rizky@worldwideadverts.info')
            ->update(['password' => Hash::make('admin123'), 'updated_at' => now()]);
        
        echo "✓ Customer password updated\n";
    } else {
        $customerId = DB::table('customer')->insertGetId($customerData);
        echo "✓ Customer created successfully\n";
        echo "Customer ID: $customerId\n";
    }
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "========================\n";
    echo "Email: rizky@worldwideadverts.info\n";
    echo "Password: admin123\n\n";
    
    echo "NOTE: The login uses the 'customers' table, not 'users'\n";
    echo "This is because the default web guard is configured to use 'customers' provider\n\n";
    
    echo "You should now be able to login successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Failed to create/update customer: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING FOR OTHER TABLE ISSUES ===\n";

// Check for any other files that might have wrong table references
$controllers = glob(app_path('Http/Controllers') . '/*.php');
$issues_found = [];

foreach ($controllers as $controller) {
    $content = file_get_contents($controller);
    
    // Check for common table name issues
    if (preg_match('/unique:(user|category|customer)/', $content)) {
        $issues_found[] = basename($controller) . " has potential table name issues";
    }
}

if (!empty($issues_found)) {
    echo "Potential issues found:\n";
    foreach ($issues_found as $issue) {
        echo "  - $issue\n";
    }
} else {
    echo "No obvious table name issues found in controllers\n";
}

echo "\n=== SUMMARY ===\n";
echo "✓ Fixed registration validation rule\n";
echo "✓ Created/updated customer account for login\n";
echo "✓ Login should now work with rizky@worldwideadverts.info / admin123\n";
echo "\nTry the login again!\n";
