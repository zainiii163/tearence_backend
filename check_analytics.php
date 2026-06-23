<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Checking vehicle analytics...\n";

// Check if table exists
echo "Vehicle analytics table exists: " . (Schema::hasTable('vehicle_analytics') ? 'Yes' : 'No') . "\n";

if (Schema::hasTable('vehicle_analytics')) {
    // Check table structure
    $columns = DB::select("DESCRIBE vehicle_analytics");
    echo "Table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column->Field . " (" . $column->Type . ")\n";
    }
} else {
    echo "Table does not exist. Checking for migration...\n";
}
