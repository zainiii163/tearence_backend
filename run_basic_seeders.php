<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== RUNNING BASIC SEEDERS ===\n\n";

$basic_seeders = [
    'CurrencySeeder',
    'LanguageSeeder', 
    'CountrySeeder',
    'ZoneSeeder'
];

foreach ($basic_seeders as $seederClass) {
    echo "Running $seederClass...\n";
    
    try {
        $seederClass = "Database\\Seeders\\{$seederClass}";
        
        if (class_exists($seederClass)) {
            $seeder = new $seederClass();
            
            if (method_exists($seeder, 'run')) {
                $seeder->run();
                echo "  ✓ SUCCESS\n";
            } else {
                echo "  ✗ No run method found\n";
            }
        } else {
            echo "  ✗ Class $seederClass not found\n";
        }
    } catch (Exception $e) {
        echo "  ✗ FAILED: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=== CHECKING TABLES AFTER SEEDING ===\n";

$tables_to_check = ['currencies', 'languages', 'countries', 'zones'];

foreach ($tables_to_check as $table) {
    // Check both singular and plural versions
    $singular_table = rtrim($table, 's');
    
    $singular_exists = DB::getSchemaBuilder()->hasTable($singular_table);
    $plural_exists = DB::getSchemaBuilder()->hasTable($table);
    
    if ($singular_exists) {
        $count = DB::table($singular_table)->count();
        echo "✓ $singular_table: $count records\n";
    } elseif ($plural_exists) {
        $count = DB::table($table)->count();
        echo "✓ $table: $count records\n";
    } else {
        echo "✗ Neither $singular_table nor $table exists\n";
    }
}

echo "\n=== CHECKING SEEDER FILES ===\n";

$seeder_path = database_path('seeders');

foreach ($basic_seeders as $seeder) {
    $file = $seeder_path . '/' . $seeder . '.php';
    if (file_exists($file)) {
        echo "✓ $seeder file exists\n";
        
        // Check what table it uses
        $content = file_get_contents($file);
        if (preg_match('/create\s*\(\s*[\'"]([^\'\"]+)[\'"]\s*\)/', $content, $matches)) {
            echo "  - Uses table: {$matches[1]}\n";
        }
    } else {
        echo "✗ $seeder file missing\n";
    }
}
