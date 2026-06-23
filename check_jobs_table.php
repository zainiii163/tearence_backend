<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Checking Jobs Table ===\n\n";

$totalJobs = \App\Models\Job::count();
echo "Total jobs: $totalJobs\n";

$activeJobs = \App\Models\Job::where('is_active', true)->count();
echo "Active jobs: $activeJobs\n";

echo "\n=== Sample Jobs (First 5) ===\n";
$jobs = \App\Models\Job::take(5)->get(['id', 'title', 'is_active', 'slug']);

foreach ($jobs as $job) {
    echo "ID: {$job->id}, Title: {$job->title}, Active: " . ($job->is_active ? 'Yes' : 'No') . ", Slug: {$job->slug}\n";
}

echo "\n=== Check completed ===\n";
