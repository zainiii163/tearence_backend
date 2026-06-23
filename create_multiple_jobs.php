<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Creating Multiple Test Jobs ===\n\n";

$jobData = [
    [
        'title' => 'Frontend Developer',
        'slug' => 'frontend-developer',
        'company_name' => 'TechStartup Inc',
        'work_type' => 'full_time',
        'experience_level' => 'mid',
        'salary_min' => 70000,
        'salary_max' => 95000,
        'salary_currency' => 'USD',
        'salary_type' => 'yearly',
        'country' => 'United States',
        'city' => 'New York',
        'description' => 'We are looking for a talented Frontend Developer to join our team.',
        'responsibilities' => 'Build responsive web applications, collaborate with designers.',
        'requirements' => '3+ years of experience with React and TypeScript.',
        'skills_needed' => 'React, TypeScript, CSS, JavaScript',
        'benefits' => 'Health insurance, remote work options.',
        'contact_email' => 'jobs@techstartup.com',
        'application_method' => 'email',
        'is_remote' => false,
    ],
    [
        'title' => 'Backend Engineer',
        'slug' => 'backend-engineer',
        'company_name' => 'CloudTech Solutions',
        'work_type' => 'full_time',
        'experience_level' => 'senior',
        'salary_min' => 100000,
        'salary_max' => 140000,
        'salary_currency' => 'USD',
        'salary_type' => 'yearly',
        'country' => 'United States',
        'city' => 'San Francisco',
        'description' => 'Looking for an experienced Backend Engineer to build scalable APIs.',
        'responsibilities' => 'Design and implement RESTful APIs, optimize database queries.',
        'requirements' => '5+ years of experience with Node.js and PostgreSQL.',
        'skills_needed' => 'Node.js, PostgreSQL, Redis, AWS',
        'benefits' => 'Competitive salary, stock options, flexible hours.',
        'contact_email' => 'careers@cloudtech.com',
        'application_method' => 'email',
        'is_remote' => true,
    ],
    [
        'title' => 'UI/UX Designer',
        'slug' => 'ui-ux-designer',
        'company_name' => 'DesignHub',
        'work_type' => 'full_time',
        'experience_level' => 'mid',
        'salary_min' => 65000,
        'salary_max' => 85000,
        'salary_currency' => 'USD',
        'salary_type' => 'yearly',
        'country' => 'Canada',
        'city' => 'Toronto',
        'description' => 'Join our design team to create beautiful user experiences.',
        'responsibilities' => 'Create wireframes, prototypes, and final designs.',
        'requirements' => '3+ years of experience with Figma and design systems.',
        'skills_needed' => 'Figma, Adobe XD, User Research',
        'benefits' => 'Creative environment, design tools provided.',
        'contact_email' => 'design@designhub.com',
        'application_method' => 'email',
        'is_remote' => true,
    ],
];

foreach ($jobData as $index => $data) {
    $job = new \App\Models\Job();
    $job->user_id = 7;
    $job->job_category_id = 14;
    $job->title = $data['title'];
    $job->slug = $data['slug'];
    $job->description = $data['description'];
    $job->responsibilities = $data['responsibilities'];
    $job->requirements = $data['requirements'];
    $job->skills_needed = $data['skills_needed'];
    $job->benefits = $data['benefits'];
    $job->company_name = $data['company_name'];
    $job->contact_email = $data['contact_email'];
    $job->application_method = $data['application_method'];
    $job->work_type = $data['work_type'];
    $job->experience_level = $data['experience_level'];
    $job->salary_min = $data['salary_min'];
    $job->salary_max = $data['salary_max'];
    $job->salary_currency = $data['salary_currency'];
    $job->salary_type = $data['salary_type'];
    $job->salary_negotiable = false;
    $job->country = $data['country'];
    $job->city = $data['city'];
    $job->is_remote = $data['is_remote'];
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

    echo "Job created: ID {$job->id} - {$job->title}\n";
}

echo "\n=== Verification ===\n";
$totalJobs = \App\Models\Job::count();
echo "Total jobs now: $totalJobs\n";
