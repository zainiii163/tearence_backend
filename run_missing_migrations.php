<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running missing migrations...\n\n";

$missing_migrations = [
    '2024_09_21_000000_create_category_table',
    '2023_10_01_000000_create_customer_table',
    '2024_01_01_000000_create_location_table',
    '2024_12_20_000000_create_currency_table',
    '2024_12_20_000001_create_language_table',
    '2024_12_20_000002_create_country_table',
    '2024_12_20_000003_create_zone_table',
    '2024_12_20_000005_create_listing_package_table',
    '2024_09_22_000000_create_listing_table'
];

foreach ($missing_migrations as $migration) {
    echo "Processing migration: $migration\n";
    
    // Remove from migrations table if it exists
    DB::table('migrations')->where('migration', $migration)->delete();
    
    // Run the migration
    try {
        $migration_class = str_replace('_', '', substr($migration, 0, -6));
        $migration_class = 'Database\\Migrations\\' . $migration_class;
        
        if (class_exists($migration_class)) {
            $instance = new $migration_class();
            $instance->up();
            
            // Record the migration
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => 999,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "✓ $migration completed successfully\n";
        } else {
            echo "✗ Class $migration_class not found\n";
        }
    } catch (Exception $e) {
        echo "✗ Error running $migration: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Migration run completed.\n";
