<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobsSeeder extends Seeder
{
    public function run()
    {
        // Create Job Categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'IT and software development jobs', 'icon' => 'laptop-code', 'sort_order' => 1],
            ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Marketing and communications roles', 'icon' => 'bullhorn', 'sort_order' => 2],
            ['name' => 'Healthcare', 'slug' => 'healthcare', 'description' => 'Medical and healthcare positions', 'icon' => 'heartbeat', 'sort_order' => 3],
            ['name' => 'Finance', 'slug' => 'finance', 'description' => 'Banking and financial services', 'icon' => 'chart-line', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            DB::table('job_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Jobs
        $jobs = [
            [
                'employer_id' => 2,
                'category_id' => 1,
                'title' => 'Senior Full Stack Developer',
                'slug' => 'senior-full-stack-developer',
                'description' => 'We are looking for an experienced Full Stack Developer to join our growing team. You will work on cutting-edge web applications using modern technologies.',
                'employment_type' => 'full-time',
                'work_type' => 'remote',
                'experience_level' => 'senior',
                'salary_min' => 80000,
                'salary_max' => 120000,
                'salary_currency' => 'USD',
                'location' => 'Remote',
                'country' => 'United States',
                'city' => 'Remote',
                'application_deadline' => Carbon::now()->addDays(30),
                'is_active' => true,
                'is_featured' => true,
                'views_count' => 245,
                'applications_count' => 18,
            ],
            [
                'employer_id' => 3,
                'category_id' => 2,
                'title' => 'Digital Marketing Manager',
                'slug' => 'digital-marketing-manager',
                'description' => 'Seeking a creative Digital Marketing Manager to develop and implement our digital marketing strategies across multiple channels.',
                'employment_type' => 'full-time',
                'work_type' => 'hybrid',
                'experience_level' => 'mid-level',
                'salary_min' => 60000,
                'salary_max' => 85000,
                'salary_currency' => 'USD',
                'location' => 'New York, NY',
                'country' => 'United States',
                'city' => 'New York',
                'application_deadline' => Carbon::now()->addDays(21),
                'is_active' => true,
                'is_featured' => false,
                'views_count' => 189,
                'applications_count' => 23,
            ],
            [
                'employer_id' => 4,
                'category_id' => 3,
                'title' => 'Registered Nurse',
                'slug' => 'registered-nurse',
                'description' => 'Join our healthcare team as a Registered Nurse. We offer competitive salaries and excellent benefits in a supportive environment.',
                'employment_type' => 'full-time',
                'work_type' => 'on-site',
                'experience_level' => 'mid-level',
                'salary_min' => 65000,
                'salary_max' => 80000,
                'salary_currency' => 'USD',
                'location' => 'Los Angeles, CA',
                'country' => 'United States',
                'city' => 'Los Angeles',
                'application_deadline' => Carbon::now()->addDays(14),
                'is_active' => true,
                'is_featured' => true,
                'views_count' => 312,
                'applications_count' => 34,
            ],
        ];

        foreach ($jobs as $job) {
            DB::table('jobs')->insert(array_merge($job, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Job Seekers
        $seekers = [
            [
                'user_id' => 5,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'professional_title' => 'Full Stack Developer',
                'bio' => 'Experienced developer with 5+ years in web development. Passionate about creating efficient and scalable applications.',
                'location' => 'San Francisco, CA',
                'country' => 'United States',
                'experience_years' => 5,
                'expected_salary_min' => 75000,
                'expected_salary_max' => 95000,
                'salary_currency' => 'USD',
                'work_type_preference' => 'remote',
                'employment_type_preference' => 'full-time',
                'is_active' => true,
                'profile_completeness' => 85,
            ],
            [
                'user_id' => 6,
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'professional_title' => 'Marketing Specialist',
                'bio' => 'Creative marketing professional with expertise in digital campaigns and brand management.',
                'location' => 'New York, NY',
                'country' => 'United States',
                'experience_years' => 3,
                'expected_salary_min' => 55000,
                'expected_salary_max' => 70000,
                'salary_currency' => 'USD',
                'work_type_preference' => 'hybrid',
                'employment_type_preference' => 'full-time',
                'is_active' => true,
                'profile_completeness' => 92,
            ],
        ];

        foreach ($seekers as $seeker) {
            DB::table('job_seekers')->insert(array_merge($seeker, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Job Applications
        $applications = [
            [
                'job_id' => 1,
                'seeker_id' => 1,
                'cover_letter' => 'I am excited to apply for the Senior Full Stack Developer position. My experience in React, Node.js, and cloud technologies aligns perfectly with your requirements.',
                'resume_path' => 'resumes/john_doe_resume.pdf',
                'status' => 'under_review',
                'applied_at' => Carbon::now()->subDays(3),
            ],
            [
                'job_id' => 2,
                'seeker_id' => 2,
                'cover_letter' => 'With my background in digital marketing and proven track record of successful campaigns, I believe I would be a valuable addition to your team.',
                'resume_path' => 'resumes/jane_smith_resume.pdf',
                'status' => 'interview_scheduled',
                'applied_at' => Carbon::now()->subDays(5),
            ],
            [
                'job_id' => 3,
                'seeker_id' => 2,
                'cover_letter' => 'While my primary experience is in marketing, I have a healthcare background and am passionate about transitioning into this field.',
                'resume_path' => 'resumes/jane_smith_healthcare_resume.pdf',
                'status' => 'rejected',
                'applied_at' => Carbon::now()->subDays(7),
            ],
        ];

        foreach ($applications as $application) {
            DB::table('job_applications')->insert(array_merge($application, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Jobs seeder completed successfully!');
    }
}
