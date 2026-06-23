<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Creating Test Job ===\n\n";

$job = new \App\Models\Job();
$job->user_id = 7;
$job->job_category_id = 14;
$job->title = 'Senior Full Stack Developer';
$job->slug = 'senior-full-stack-developer';
$job->description = 'We are looking for an experienced Full Stack Developer to join our growing team. You will be responsible for developing and maintaining web applications using modern technologies.';
$job->responsibilities = 'Develop and maintain web applications, collaborate with cross-functional teams, write clean and efficient code.';
$job->requirements = '5+ years of experience in web development, proficiency in React, Node.js, and PostgreSQL.';
$job->skills_needed = 'React, Node.js, PostgreSQL, AWS, JavaScript, TypeScript, Git, Docker';
$job->benefits = 'Competitive salary, health insurance, flexible work hours, remote work options.';
$job->company_name = 'TechCorp Solutions';
$job->company_website = 'https://techcorp.example.com';
$job->contact_email = 'hr@techcorp.example.com';
$job->application_method = 'email';
$job->work_type = 'full_time';
$job->experience_level = 'senior';
$job->salary_min = 90000;
$job->salary_max = 120000;
$job->salary_currency = 'USD';
$job->salary_type = 'yearly';
$job->salary_negotiable = false;
$job->country = 'United States';
$job->city = 'San Francisco';
$job->is_remote = true;
$job->is_urgent = false;
$job->verified_employer = true;
$job->is_active = true;
$job->is_featured = false;
$job->is_sponsored = false;
$job->is_promoted = false;
$job->expires_at = now()->addDays(30);
$job->views_count = 0;
$job->applications_count = 0;
$job->saves_count = 0;
$job->save();

echo "Job created successfully!\n";
echo "ID: {$job->id}\n";
echo "Title: {$job->title}\n";
echo "Slug: {$job->slug}\n";
echo "Active: " . ($job->is_active ? 'Yes' : 'No') . "\n";

echo "\n=== Verification ===\n";
$totalJobs = \App\Models\Job::count();
echo "Total jobs now: $totalJobs\n";
