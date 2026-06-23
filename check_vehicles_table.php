<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking vehicles table structure...\n\n";

if (DB::getSchemaBuilder()->hasTable('vehicles')) {
    $columns = DB::getSchemaBuilder()->getColumnListing('vehicles');
    echo 'Vehicles table columns: ' . implode(', ', $columns) . "\n";
    
    // Check if id column exists
    $hasId = in_array('id', $columns);
    echo 'Has id column: ' . ($hasId ? 'YES' : 'NO') . "\n";
    
    if ($hasId) {
        try {
            $idType = DB::connection()->getDoctrineColumn('vehicles', 'id')->getType()->getName();
            echo 'ID column type: ' . $idType . "\n";
        } catch (Exception $e) {
            echo 'Could not get ID column type: ' . $e->getMessage() . "\n";
        }
    }
    
    // Check for vehicle_id column
    $hasVehicleId = in_array('vehicle_id', $columns);
    echo 'Has vehicle_id column: ' . ($hasVehicleId ? 'YES' : 'NO') . "\n";
    
    // Get count
    $count = DB::table('vehicles')->count();
    echo 'Records in vehicles table: ' . $count . "\n";
    
} else {
    echo 'Vehicles table does not exist\n';
}

echo "\nChecking migration order...\n";

// Check which migrations have run
$migration_check = [
    '2026_03_10_000003_create_vehicles_table',
    '2026_03_07_211710_create_vehicle_images_table'
];

foreach ($migration_check as $migration) {
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    echo $migration . ': ' . ($exists ? 'RUN' : 'NOT RUN') . "\n";
}
