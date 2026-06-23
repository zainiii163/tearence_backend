<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running migrations manually to bypass foreign key issues...\n\n";

// Get all migration files
$migrationPath = database_path('migrations');
$files = glob($migrationPath . '/*.php');

// Sort files by date
sort($files);

$failed_migrations = [];
$successful_migrations = [];

foreach ($files as $file) {
    $migration_name = basename($file, '.php');
    
    // Skip if already run
    if (DB::table('migrations')->where('migration', $migration_name)->exists()) {
        echo "✓ Skipping $migration_name (already run)\n";
        continue;
    }
    
    echo "Running $migration_name...\n";
    
    try {
        // Include the migration file
        require_once $file;
        
        // Get the migration class (anonymous class)
        $migration = require $file;
        
        if (is_object($migration) && method_exists($migration, 'up')) {
            // Special handling for vehicle_images to skip foreign key
            if ($migration_name === '2026_03_07_211710_create_vehicle_images_table') {
                echo "  Special handling for vehicle_images (skipping foreign key)\n";
                
                // Create table without foreign key
                DB::statement("CREATE TABLE vehicle_images (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    vehicle_id BIGINT UNSIGNED NOT NULL,
                    image_path VARCHAR(255) NOT NULL,
                    alt_text VARCHAR(255) NULL,
                    is_main BOOLEAN DEFAULT 0,
                    sort_order INT DEFAULT 0,
                    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX vehicle_images_vehicle_id_is_main_index (vehicle_id, is_main),
                    INDEX vehicle_images_vehicle_id_sort_order_index (vehicle_id, sort_order)
                )");
            } else {
                $migration->up();
            }
            
            // Record the migration
            DB::table('migrations')->insert([
                'migration' => $migration_name,
                'batch' => 1000,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            echo "  ✓ SUCCESS\n";
            $successful_migrations[] = $migration_name;
        } else {
            echo "  ✗ Invalid migration format\n";
            $failed_migrations[] = $migration_name;
        }
    } catch (Exception $e) {
        echo "  ✗ FAILED: " . $e->getMessage() . "\n";
        $failed_migrations[] = $migration_name;
        
        // If this is a foreign key error, try to continue
        if (strpos($e->getMessage(), 'Foreign key constraint') !== false) {
            echo "  Foreign key error detected, continuing...\n";
        }
    }
    
    echo "\n";
}

echo "=== MIGRATION SUMMARY ===\n";
echo "Successful: " . count($successful_migrations) . "\n";
echo "Failed: " . count($failed_migrations) . "\n";

if (!empty($failed_migrations)) {
    echo "\nFailed migrations:\n";
    foreach ($failed_migrations as $failed) {
        echo "  - $failed\n";
    }
}

echo "\nChecking critical tables after migration...\n";

$critical_tables = [
    'category' => 'Category (singular)',
    'categories' => 'Categories (plural)', 
    'customer' => 'Customer (singular)',
    'customers' => 'Customers (plural)',
    'vehicles' => 'Vehicles',
    'vehicle_images' => 'Vehicle Images'
];

foreach ($critical_tables as $table => $description) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    $count = $exists ? DB::table($table)->count() : 0;
    echo $exists ? "✓" : "✗";
    echo " $table: $count records ($description)\n";
}
