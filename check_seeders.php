<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking seeders status...\n\n";

// Check if key tables have data
$tables_to_check = [
    'categories' => 'Category',
    'listings' => 'Listing', 
    'customers' => 'Customer',
    'users' => 'User',
    'countries' => 'Country',
    'currencies' => 'Currency',
    'languages' => 'Language',
    'zones' => 'Zone',
    'packages' => 'Package',
    'service_categories' => 'ServiceCategory',
    'sponsored_categories' => 'SponsoredCategory',
    'banner_categories' => 'BannerCategory',
    'buysell_categories' => 'BuySellCategory',
    'job_categories' => 'JobCategory',
    'vehicle_categories' => 'VehicleCategory',
    'property_categories' => 'PropertyCategory'
];

echo "Table data status:\n";
echo "-----------------\n";

foreach ($tables_to_check as $table => $model) {
    try {
        if (DB::getSchemaBuilder()->hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✓ $table: $count records\n";
        } else {
            echo "✗ $table: Table missing\n";
        }
    } catch (Exception $e) {
        echo "? $table: Error - " . $e->getMessage() . "\n";
    }
}

echo "\nChecking seeder files...\n";
echo "---------------------\n";

$seederPath = database_path('seeders');
$seederFiles = glob($seederPath . '/*.php');

echo "Total seeder files: " . count($seederFiles) . "\n\n";

// Check main DatabaseSeeder
$databaseSeeder = file_get_contents($seederPath . '/DatabaseSeeder.php');
echo "Main DatabaseSeeder calls:\n";
preg_match_all('/([A-Za-z]+Seeder::class)/', $databaseSeeder, $matches);
if (!empty($matches[1])) {
    foreach ($matches[1] as $seeder) {
        echo "  - $seeder\n";
    }
}

echo "\nRecommending seeder execution order...\n";
echo "------------------------------------\n";

// Check which seeders should be run based on missing tables
$missingTables = [];
foreach ($tables_to_check as $table => $model) {
    if (!DB::getSchemaBuilder()->hasTable($table)) {
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "Missing tables detected. You may need to run migrations first:\n";
    foreach ($missingTables as $table) {
        echo "  - $table\n";
    }
} else {
    echo "All tables exist. Checking for empty tables that need seeding...\n";
    
    foreach ($tables_to_check as $table => $model) {
        try {
            $count = DB::table($table)->count();
            if ($count == 0) {
                echo "  - $table (empty - needs seeding)\n";
            }
        } catch (Exception $e) {
            // Skip
        }
    }
}

echo "\nCheck completed.\n";
