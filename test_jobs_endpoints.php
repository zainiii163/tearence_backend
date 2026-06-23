<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Jobs Endpoints Diagnostic ===\n\n";

// Test 1: Check if jobs table exists
echo "1. Checking jobs table...\n";
try {
    $jobsCount = \DB::table('jobs')->count();
    echo "   ✓ jobs table exists ({$jobsCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ jobs table ERROR: " . $e->getMessage() . "\n";
}

// Test 2: Check if job_categories table exists
echo "\n2. Checking job_categories table...\n";
try {
    $categoriesCount = \DB::table('job_categories')->count();
    echo "   ✓ job_categories table exists ({$categoriesCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ job_categories table ERROR: " . $e->getMessage() . "\n";
}

// Test 3: Check if job_seekers table exists
echo "\n3. Checking job_seekers table...\n";
try {
    $seekersCount = \DB::table('job_seekers')->count();
    echo "   ✓ job_seekers table exists ({$seekersCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ job_seekers table ERROR: " . $e->getMessage() . "\n";
}

// Test 4: Check if job_applications table exists
echo "\n4. Checking job_applications table...\n";
try {
    $applicationsCount = \DB::table('job_applications')->count();
    echo "   ✓ job_applications table exists ({$applicationsCount} records)\n";
} catch (\Exception $e) {
    echo "   ✗ job_applications table ERROR: " . $e->getMessage() . "\n";
}

// Test 5: Check Job model
echo "\n5. Testing Job model...\n";
try {
    $jobs = \App\Models\Job::with(['category'])->limit(1)->get();
    echo "   ✓ Job model works ({$jobs->count()} jobs)\n";
} catch (\Exception $e) {
    echo "   ✗ Job model ERROR: " . $e->getMessage() . "\n";
}

// Test 6: Check JobCategory model
echo "\n6. Testing JobCategory model...\n";
try {
    $categories = \App\Models\JobCategory::where('is_active', true)->get();
    echo "   ✓ JobCategory model works ({$categories->count()} active categories)\n";
} catch (\Exception $e) {
    echo "   ✗ JobCategory model ERROR: " . $e->getMessage() . "\n";
}

// Test 7: Test V1 JobController index
echo "\n7. Testing V1 JobController index...\n";
try {
    $controller = new \App\Http\Controllers\Api\V1\JobController();
    $request = new \Illuminate\Http\Request(['sort_by' => 'newest', 'per_page' => 12]);
    $response = $controller->index($request);
    echo "   ✓ V1 JobController::index works\n";
    echo "   Response: " . substr($response->getContent(), 0, 200) . "...\n";
} catch (\Exception $e) {
    echo "   ✗ V1 JobController::index ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 8: Test V1 JobController statistics
echo "\n8. Testing V1 JobController statistics...\n";
try {
    $controller = new \App\Http\Controllers\Api\V1\JobController();
    $response = $controller->statistics();
    echo "   ✓ V1 JobController::statistics works\n";
    echo "   Response: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "   ✗ V1 JobController::statistics ERROR: " . $e->getMessage() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}

// Test 9: Check jobs table structure
echo "\n9. Checking jobs table structure...\n";
try {
    $columns = \DB::select("DESCRIBE jobs");
    echo "   ✓ jobs table has " . count($columns) . " columns:\n";
    foreach ($columns as $column) {
        echo "      - {$column->Field} ({$column->Type})\n";
    }
} catch (\Exception $e) {
    echo "   ✗ jobs structure ERROR: " . $e->getMessage() . "\n";
}

// Test 10: Check job_categories table structure
echo "\n10. Checking job_categories table structure...\n";
try {
    $columns = \DB::select("DESCRIBE job_categories");
    echo "   ✓ job_categories table has " . count($columns) . " columns:\n";
    foreach ($columns as $column) {
        echo "      - {$column->Field} ({$column->Type})\n";
    }
} catch (\Exception $e) {
    echo "   ✗ job_categories structure ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
