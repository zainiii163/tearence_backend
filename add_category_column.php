<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Add the category_id column directly
    DB::statement('ALTER TABLE `customer_business` ADD COLUMN `category_id` INT(10) UNSIGNED NULL AFTER `customer_id`');
    echo "SUCCESS: category_id column added to customer_business table\n";
} catch (\Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "INFO: category_id column already exists\n";
    } else {
        echo "ERROR: " . $e->getMessage() . "\n";
        exit(1);
    }
}
