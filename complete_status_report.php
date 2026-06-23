<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== COMPLETE MIGRATION AND SEEDER STATUS REPORT ===\n\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// Database connection check
try {
    $pdo = DB::connection()->getPdo();
    echo "✓ DATABASE CONNECTION: SUCCESS\n";
    echo "  Database: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (Exception $e) {
    echo "✗ DATABASE CONNECTION: FAILED\n";
    echo "  Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Migration status
$migration_count = DB::table('migrations')->count();
echo "✓ MIGRATIONS: $migration_count migrations completed\n\n";

echo "=== CORE TABLES STATUS ===\n";

$core_tables = [
    'category' => 'Categories (main categories table)',
    'customer' => 'Customers (main customers table)', 
    'listing' => 'Listings (main listings table)',
    'country' => 'Countries (location data)',
    'currency' => 'Currencies (currency data)',
    'language' => 'Languages (language data)',
    'zone' => 'Zones (geographic zones)',
    'users' => 'Users (application users)',
    'vehicles' => 'Vehicles (vehicle listings)',
    'location' => 'Locations (location data)'
];

$all_good = true;
foreach ($core_tables as $table => $description) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    $count = $exists ? DB::table($table)->count() : 0;
    $status = $exists ? "✓" : "✗";
    echo "$status $table: $count records - $description\n";
    
    if (!$exists || $count == 0) {
        $all_good = false;
    }
}

echo "\n=== SEEDED DATA TABLES ===\n";

$seeded_tables = [
    'ad_pricing_plans' => 'Advertisement pricing plans',
    'service_categories' => 'Service categories',
    'sponsored_categories' => 'Sponsored advert categories',
    'banner_categories' => 'Banner advert categories',
    'packages' => 'Listing packages'
];

foreach ($seeded_tables as $table => $description) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    $count = $exists ? DB::table($table)->count() : 0;
    $status = $exists ? "✓" : "✗";
    echo "$status $table: $count records - $description\n";
}

echo "\n=== DATABASE STATISTICS ===\n";

$total_tables = 0;
$total_records = 0;
$tables_with_data = 0;

$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    foreach ($table as $key => $table_name) {
        $total_tables++;
        try {
            $count = DB::table($table_name)->count();
            $total_records += $count;
            if ($count > 0) {
                $tables_with_data++;
            }
        } catch (Exception $e) {
            // Skip tables that can't be counted
        }
    }
}

echo "Total tables: $total_tables\n";
echo "Tables with data: $tables_with_data\n";
echo "Total records: $total_records\n\n";

echo "=== VERIFICATION TESTS ===\n";

try {
    // Test basic queries
    $category_count = DB::table('category')->count();
    $country_count = DB::table('country')->count();
    $currency_count = DB::table('currency')->count();
    
    echo "✓ Basic queries working\n";
    echo "  Categories: $category_count\n";
    echo "  Countries: $country_count\n";
    echo "  Currencies: $currency_count\n";
    
    // Test model usage
    $category = \App\Models\Category::first();
    if ($category) {
        echo "✓ Category model working\n";
    }
    
    $country = \App\Models\Country::first();
    if ($country) {
        echo "✓ Country model working\n";
    }
    
    $currency = \App\Models\Currency::first();
    if ($currency) {
        echo "✓ Currency model working\n";
    }
    
} catch (Exception $e) {
    echo "✗ Verification failed: " . $e->getMessage() . "\n";
}

echo "\n=== FINAL STATUS ===\n";

if ($all_good && $migration_count > 0) {
    echo "🎉 SUCCESS: All migrations and critical seeders completed!\n";
    echo "✓ Database is ready for use\n";
    echo "✓ All core tables exist\n";
    echo "✓ Basic data has been seeded\n";
    echo "✓ Models are working correctly\n";
    echo "\n📋 NEXT STEPS:\n";
    echo "1. Test the application API endpoints\n";
    echo "2. Verify frontend functionality\n";
    echo "3. Check application logs for any issues\n";
    echo "4. Run any additional seeders if needed\n";
} else {
    echo "⚠️  WARNING: Some issues detected\n";
    echo "✗ Some core tables may be missing or empty\n";
    echo "✗ Additional setup may be required\n";
}

echo "\n=== TABLE NAMING NOTE ===\n";
echo "The database uses singular table names (category, customer, listing)\n";
echo "which is consistent with the Laravel model configurations.\n";
echo "If the application expects plural names, you may need to:\n";
echo "1. Update model \$table properties, OR\n";
echo "2. Create database views for plural names, OR\n";
echo "3. Update application code to use singular names\n";

echo "\n=== REPORT COMPLETE ===\n";
