<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== MIGRATION AND SEEDER STATUS REPORT ===\n\n";

// Check database connection
try {
    $pdo = DB::connection()->getPdo();
    echo "✓ Database connection: SUCCESS\n";
    echo "✓ Database: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (Exception $e) {
    echo "✗ Database connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check migration status
$migrations = DB::table('migrations')->orderBy('batch', 'asc')->orderBy('id', 'asc')->get();
echo "Total migrations in table: " . $migrations->count() . "\n\n";

// Check critical tables
$critical_tables = [
    'category' => 'Category (singular)',
    'categories' => 'Categories (plural)',
    'customer' => 'Customer (singular)',
    'customers' => 'Customers (plural)',
    'location' => 'Location (singular)',
    'locations' => 'Locations (plural)',
    'listing' => 'Listing (singular)',
    'listings' => 'Listings (plural)',
    'countries' => 'Countries',
    'currencies' => 'Currencies',
    'languages' => 'Languages',
    'zones' => 'Zones'
];

echo "Critical table status:\n";
echo "---------------------\n";
foreach ($critical_tables as $table => $description) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    $count = $exists ? DB::table($table)->count() : 0;
    echo $exists ? "✓" : "✗";
    echo " $table: $count records ($description)\n";
}

echo "\n=== RECOMMENDATIONS ===\n\n";

$missing_critical = [];
foreach ($critical_tables as $table => $description) {
    if (!DB::getSchemaBuilder()->hasTable($table)) {
        $missing_critical[] = $table;
    }
}

if (!empty($missing_critical)) {
    echo "ISSUES FOUND:\n";
    echo "------------\n";
    echo "1. Critical tables are missing: " . implode(', ', $missing_critical) . "\n";
    echo "2. Some migrations may have failed or been rolled back\n\n";
    
    echo "RECOMMENDED SOLUTIONS:\n";
    echo "----------------------\n";
    echo "Option 1: Fresh start (RECOMMENDED)\n";
    echo "  php artisan migrate:fresh --seed\n";
    echo "  This will drop all tables and recreate everything from scratch\n\n";
    
    echo "Option 2: Manual migration fix\n";
    echo "  1. Check migration files for correct table names\n";
    echo "  2. Run individual migrations that failed\n";
    echo "  3. Run seeders to populate data\n\n";
    
    echo "Option 3: Check for migration errors\n";
    echo "  Look in Laravel logs for any migration errors\n";
    echo "  Check if migrations were interrupted\n\n";
} else {
    echo "✓ All critical tables exist\n";
    
    // Check if tables have data
    $empty_tables = [];
    foreach ($critical_tables as $table => $description) {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            $count = DB::table($table)->count();
            if ($count == 0) {
                $empty_tables[] = $table;
            }
        }
    }
    
    if (!empty($empty_tables)) {
        echo "Empty tables that need seeding: " . implode(', ', $empty_tables) . "\n";
        echo "Run: php artisan db:seed\n";
    } else {
        echo "✓ All tables have data\n";
    }
}

echo "\n=== SEEDER STATUS ===\n\n";

// Check seeder classes
$seeder_path = database_path('seeders');
$seeders_to_check = [
    'DatabaseSeeder.php',
    'CategorySeeder.php',
    'CustomerSeeder.php',
    'CountrySeeder.php',
    'CurrencySeeder.php',
    'LanguageSeeder.php',
    'ZoneSeeder.php'
];

echo "Checking seeder files:\n";
foreach ($seeders_to_check as $seeder) {
    $full_path = $seeder_path . '/' . $seeder;
    $exists = file_exists($full_path);
    echo $exists ? "✓" : "✗";
    echo " $seeder\n";
}

echo "\n=== NEXT STEPS ===\n\n";
echo "Based on the analysis above, here's what you should do:\n\n";
echo "1. BACKUP your database if you have important data\n";
echo "2. Run: php artisan migrate:fresh --seed\n";
echo "3. If that fails due to Termwind error, try:\n";
echo "   - composer update nunomaduro/termwind\n";
echo "   - Or use: php artisan migrate:fresh --seed --no-ansi\n";
echo "4. Verify all tables are created and populated\n";
echo "5. Test the application functionality\n\n";

echo "If you encounter issues, check:\n";
echo "- Laravel storage/logs/laravel.log for errors\n";
echo "- Database permissions\n";
echo "- PHP extensions (mbstring, pdo_mysql, etc.)\n";
