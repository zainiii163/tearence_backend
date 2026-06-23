<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== RUNNING SEEDERS ===\n\n";

// Define the seeders to run in order
$seeders = [
    'CurrencySeeder',
    'LanguageSeeder', 
    'CountrySeeder',
    'ZoneSeeder',
    'CategorySeeder',
    'PackageSeeder',
    'AdPricingPlansSeeder',
    'BuySellCategorySeeder',
    'BuySellPromotionPlanSeeder',
    'BuySellAdvertSeeder',
    'PricingPlanSeeder',
    'BookAdvertSeeder',
    'ServiceCategorySeeder',
    'ServiceSeeder',
    'SponsoredCategorySeeder',
    'SponsoredPricingPlanSeeder',
    'SponsoredAdvertSeeder',
    'BannerCategorySeeder',
    'BannerAdSeeder',
    'SampleListingsSeeder',
    'AllCategoryPostsSeeder',
    'ListingSeeder',
    'CandidateProfileSeeder',
    'JobAlertSeeder',
    'JobUpsellSeeder',
    'CandidateUpsellSeeder',
    'RevenueTrackingSeeder'
];

$successful_seeders = [];
$failed_seeders = [];

foreach ($seeders as $seederClass) {
    echo "Running $seederClass...\n";
    
    try {
        $seederClass = "Database\\Seeders\\{$seederClass}";
        
        if (class_exists($seederClass)) {
            $seeder = new $seederClass();
            
            // Check if this seeder expects plural tables
            if (method_exists($seeder, 'run')) {
                $seeder->run();
                echo "  ✓ SUCCESS\n";
                $successful_seeders[] = $seederClass;
            } else {
                echo "  ✗ No run method found\n";
                $failed_seeders[] = $seederClass;
            }
        } else {
            echo "  ✗ Class $seederClass not found\n";
            $failed_seeders[] = $seederClass;
        }
    } catch (Exception $e) {
        echo "  ✗ FAILED: " . $e->getMessage() . "\n";
        $failed_seeders[] = $seederClass;
    }
    
    echo "\n";
}

echo "=== SEEDER SUMMARY ===\n";
echo "Successful: " . count($successful_seeders) . "\n";
echo "Failed: " . count($failed_seeders) . "\n";

if (!empty($failed_seeders)) {
    echo "\nFailed seeders:\n";
    foreach ($failed_seeders as $failed) {
        echo "  - $failed\n";
    }
}

echo "\n=== CHECKING TABLE DATA ===\n";

// Check critical tables after seeding
$critical_tables = [
    'category' => 'Category (singular)',
    'categories' => 'Categories (plural)',
    'customer' => 'Customer (singular)', 
    'customers' => 'Customers (plural)',
    'countries' => 'Countries',
    'currencies' => 'Currencies',
    'languages' => 'Languages',
    'zones' => 'Zones',
    'listings' => 'Listings',
    'vehicles' => 'Vehicles'
];

foreach ($critical_tables as $table => $description) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    $count = $exists ? DB::table($table)->count() : 0;
    echo $exists ? "✓" : "✗";
    echo " $table: $count records ($description)\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. If you see singular tables with data but plural tables are missing,\n";
echo "   the seeders are working but the models expect plural table names.\n";
echo "2. You may need to update the model \$table property or create views.\n";
echo "3. Check the application code to see which table names are expected.\n";
