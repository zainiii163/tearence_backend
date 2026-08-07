<?php
/**
 * Safe local migrate: run pending migrations one-by-one.
 * If schema objects already exist, record the migration as Ran and continue.
 */
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

$migrationsPath = database_path('migrations');
$files = collect(File::files($migrationsPath))
    ->map(fn ($f) => pathinfo($f->getFilename(), PATHINFO_FILENAME))
    ->sort()
    ->values();

$ran = DB::table('migrations')->pluck('migration')->flip();
$pending = $files->filter(fn ($name) => !$ran->has($name))->values();

$maxBatch = (int) (DB::table('migrations')->max('batch') ?? 0);
$ok = 0;
$skipped = 0;
$failed = [];

$skipPatterns = [
    'already exists',
    'Duplicate column',
    'Duplicate key name',
    'Can\'t DROP',
    'check that column/key exists',
    'Cannot add foreign key constraint',
    'errno: 121', // duplicate FK
];

echo "Pending: {$pending->count()}\n";

foreach ($pending as $name) {
    $rel = 'database/migrations/' . $name . '.php';
    if (!File::exists(base_path($rel))) {
        echo "MISSING FILE: $name\n";
        $failed[] = [$name, 'file missing'];
        continue;
    }

    echo ">>> $name ... ";
    try {
        $code = Artisan::call('migrate', [
            '--path' => $rel,
            '--force' => true,
        ]);
        $output = Artisan::output();
        if ($code === 0) {
            echo "OK\n";
            $ok++;
            continue;
        }
        throw new RuntimeException(trim($output) ?: "exit $code");
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        $shouldSkip = false;
        foreach ($skipPatterns as $p) {
            if (stripos($msg, $p) !== false) {
                $shouldSkip = true;
                break;
            }
        }

        // Also skip if create table and table now exists / alter already applied
        if (!$shouldSkip && preg_match('/Table \'[^\']+\.([^\']+)\' doesn\'t exist/i', $msg, $m)) {
            // missing dependency table — real failure
            $shouldSkip = false;
        }

        if ($shouldSkip) {
            $maxBatch++;
            // Only insert if not already recorded (partial runs)
            if (!DB::table('migrations')->where('migration', $name)->exists()) {
                DB::table('migrations')->insert([
                    'migration' => $name,
                    'batch' => $maxBatch,
                ]);
            }
            echo "SKIP (already applied)\n";
            $skipped++;
        } else {
            $short = preg_replace('/\s+/', ' ', substr($msg, 0, 220));
            echo "FAIL: $short\n";
            $failed[] = [$name, $short];
        }
    }
}

echo "\nDone. OK=$ok SKIP=$skipped FAIL=" . count($failed) . "\n";
if ($failed) {
    echo "\nFailures:\n";
    foreach ($failed as [$n, $m]) {
        echo " - $n\n   $m\n";
    }
    exit(1);
}

exit(0);
