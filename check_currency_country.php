<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'Checking currency table:' . PHP_EOL;
$currency_exists = DB::getSchemaBuilder()->hasTable('currency');
echo $currency_exists ? 'EXISTS' : 'MISSING';
if ($currency_exists) {
    echo ' - Records: ' . DB::table('currency')->count();
}
echo PHP_EOL;

echo 'Checking country table:' . PHP_EOL;
$country_exists = DB::getSchemaBuilder()->hasTable('country');
echo $country_exists ? 'EXISTS' : 'MISSING';
if ($country_exists) {
    echo ' - Records: ' . DB::table('country')->count();
}
echo PHP_EOL;

echo PHP_EOL . 'All tables in database:' . PHP_EOL;
$tables = DB::select('SHOW TABLES');
foreach ($tables as $table) {
    foreach ($table as $key => $value) {
        echo $value . PHP_EOL;
    }
}
