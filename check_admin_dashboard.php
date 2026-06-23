<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ADMIN DASHBOARD CHECK ===\n\n";

// Check vehicles table
$vehicles_exist = DB::getSchemaBuilder()->hasTable('vehicles');
echo "Vehicles table: " . ($vehicles_exist ? "EXISTS" : "MISSING") . "\n";

if ($vehicles_exist) {
    $vehicle_count = DB::table('vehicles')->count();
    echo "Vehicle records: $vehicle_count\n";
    
    if ($vehicle_count > 0) {
        echo "Sample vehicle data:\n";
        $sample = DB::table('vehicles')->first();
        echo "  ID: " . $sample->id . "\n";
        echo "  Title: " . $sample->title . "\n";
        echo "  User ID: " . $sample->user_id . "\n";
        echo "  Category ID: " . $sample->category_id . "\n";
    }
}

// Check related tables
$related_tables = ['users', 'vehicle_categories', 'vehicle_makes', 'vehicle_models'];
echo "\nRelated tables:\n";
foreach ($related_tables as $table) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    $count = $exists ? DB::table($table)->count() : 0;
    echo "  $table: " . ($exists ? "EXISTS" : "MISSING") . " - $count records\n";
}

echo "\n=== WIDGET REQUIREMENTS ===\n";

// Check what the widgets need
echo "VehicleStatsChart needs:\n";
echo "  - Vehicle data with created_at dates\n";
echo "  - Last 7 days of data\n\n";

echo "RecentVehiclesWidget needs:\n";
echo "  - Vehicle data with relationships:\n";
echo "    - user (vehicles.user_id → users.id)\n";
echo "    - category (vehicles.category_id → vehicle_categories.id)\n";
echo "    - make (vehicles.make_id → vehicle_makes.id)\n";
echo "    - vehicleModel (vehicles.model_id → vehicle_models.id)\n";
echo "  - main_image column\n\n";

echo "=== POTENTIAL ISSUES ===\n";

if (!$vehicles_exist) {
    echo "❌ Vehicles table doesn't exist\n";
} elseif (DB::table('vehicles')->count() == 0) {
    echo "⚠️  Vehicles table exists but is empty - widgets will show no data\n";
} else {
    echo "✅ Vehicles table has data\n";
    
    // Check relationships
    $sample_vehicle = DB::table('vehicles')->first();
    
    if ($sample_vehicle->user_id && !DB::table('users')->where('id', $sample_vehicle->user_id)->exists()) {
        echo "⚠️  Vehicle references non-existent user ID: " . $sample_vehicle->user_id . "\n";
    }
    
    if ($sample_vehicle->category_id && !DB::table('vehicle_categories')->where('id', $sample_vehicle->category_id)->exists()) {
        echo "⚠️  Vehicle references non-existent category ID: " . $sample_vehicle->category_id . "\n";
    }
}

echo "\n=== RECOMMENDATIONS ===\n";

if (DB::table('vehicles')->count() == 0) {
    echo "1. Create some sample vehicle data\n";
    echo "2. Run VehicleSeeder if it exists\n";
    echo "3. Or the dashboard will just show empty widgets (which is fine)\n";
} else {
    echo "1. Check widget error logs for specific issues\n";
    echo "2. Verify all relationship foreign keys exist\n";
    echo "3. Test dashboard access directly\n";
}

echo "\n=== ADMIN ACCESS TEST ===\n";

// Check if user can access admin panel
$user = DB::table('users')->where('email', 'rizky@worldwideadverts.info')->first();
if ($user) {
    echo "✅ Admin user exists\n";
    
    // Check User model canAccessPanel method
    try {
        $user_model = new \App\Models\User();
        $can_access = $user_model->canAccessPanel(null);
        echo "Admin panel access: " . ($can_access ? "GRANTED" : "DENIED") . "\n";
    } catch (Exception $e) {
        echo "Could not check admin access: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Admin user not found in users table\n";
    
    // Check customer table
    $customer = DB::table('customer')->where('email', 'rizky@worldwideadverts.info')->first();
    if ($customer) {
        echo "ℹ️  User exists in customer table (for frontend login)\n";
        echo "ℹ️  Admin panel uses users table (different authentication)\n";
        echo "💡 You may need to create a user in the users table for admin access\n";
    }
}

echo "\nTry accessing: http://127.0.0.1:8000/admin\n";
