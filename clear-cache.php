<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Artisan::call('cache:clear');
\Artisan::call('config:clear');
\Artisan::call('route:clear');
\Artisan::call('view:clear');

echo "All caches cleared successfully!";
?>
