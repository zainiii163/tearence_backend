<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$check_migrations = [
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

echo "Checking specific migrations:\n";
echo "---------------------------\n";

foreach ($check_migrations as $migration) {
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    echo $migration . ': ' . ($exists ? 'RUN' : 'NOT RUN') . "\n";
}

echo "\nChecking if tables exist:\n";
echo "-------------------------\n";

$tables_to_check = [
    'categories',
    'customers',
    'locations',
    'currencies',
    'languages',
    'countries',
    'zones',
    'listing_packages',
    'listings'
];

foreach ($tables_to_check as $table) {
    $exists = DB::getSchemaBuilder()->hasTable($table);
    echo $table . ': ' . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}
