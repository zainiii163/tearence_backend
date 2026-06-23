<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check database connection
try {
    $pdo = DB::connection()->getPdo();
    echo "Database connection: SUCCESS\n";
    echo "Database name: " . DB::connection()->getDatabaseName() . "\n\n";
} catch (Exception $e) {
    echo "Database connection: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Check migrations table
try {
    $migrations = DB::table('migrations')->orderBy('batch', 'asc')->orderBy('id', 'asc')->get();
    echo "Total migrations run: " . $migrations->count() . "\n\n";
    
    if ($migrations->count() > 0) {
        echo "Migration status:\n";
        echo "------------------\n";
        foreach ($migrations as $migration) {
            echo "✓ " . $migration->migration . " (Batch: " . $migration->batch . ")\n";
        }
    } else {
        echo "No migrations have been run yet.\n";
    }
} catch (Exception $e) {
    echo "Could not check migrations table: " . $e->getMessage() . "\n";
    echo "This might mean the migrations table doesn't exist yet.\n";
}

// Check if all migration files exist in the filesystem
$migrationPath = database_path('migrations');
$files = glob($migrationPath . '/*.php');
echo "\nTotal migration files: " . count($files) . "\n";

// Check some key tables
$tables_to_check = [
    'users',
    'categories', 
    'listings',
    'customers',
    'migrations',
    'password_resets',
    'failed_jobs',
    'personal_access_tokens'
];

echo "\nKey table status:\n";
echo "-----------------\n";
foreach ($tables_to_check as $table) {
    try {
        $exists = DB::getSchemaBuilder()->hasTable($table);
        echo $exists ? "✓ $table exists\n" : "✗ $table missing\n";
    } catch (Exception $e) {
        echo "? $table - Error checking: " . $e->getMessage() . "\n";
    }
}

echo "\nCheck completed.\n";
