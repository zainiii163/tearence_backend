<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing jobs
        DB::table('jobs')->delete();

        $categories = JobCategory::all();
        $users = User::where('user_id', '>', 1)->take(10)->get();

        if ($categories->isEmpty() || $users->isEmpty()) {
            $this->command->info('No categories or users found. Please run other seeders first.');
            return;
        }

        $sampleJobs = [
            [
                'title' => 'Senior Full Stack Developer',
                'description' => 'We are looking for an experienced Full Stack Developer to join our growing team. You will be responsible for developing and maintaining web applications using modern technologies.',
                'responsibilities' => 'Develop and maintain web applications, collaborate with cross-functional teams, write clean and efficient code, participate in code reviews, and mentor junior developers.',
                'requirements' => '5+ years of experience in web development, proficiency in React, Node.js, and PostgreSQL, experience with cloud services (AWS/Azure), strong problem-solving skills.',
                'benefits' => 'Competitive salary, health insurance, flexible work hours, remote work options, professional development opportunities, and great company culture.',
                'skills_needed' => 'React, Node.js, PostgreSQL, AWS, JavaScript, TypeScript, Git, Docker',
                'company_name' => 'TechCorp Solutions',
                'company_website' => 'https://techcorp.example.com',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'education_level' => 'bachelor',
                'salary_min' => 90000,
                'salary_max' => 120000,
                'salary_currency' => 'USD',
                'salary_type' => 'yearly',
                'country' => 'United States',
                'city' => 'San Francisco',
                'is_remote' => 1,
                'is_verified_employer' => 1,
                'is_active' => 1,
                'is_featured' => 1,
                'expires_at' => Carbon::now()->addDays(30),
            ],
            [
                'title' => 'Marketing Manager',
                'description' => 'We are seeking a creative Marketing Manager to develop and implement our marketing strategy. You will lead a team and drive brand awareness across multiple channels.',
                'responsibilities' => 'Develop marketing strategies, manage marketing campaigns, lead the marketing team, analyze market trends, and work with sales to drive revenue.',
                'requirements' => '3+ years of marketing experience, strong leadership skills, experience with digital marketing, excellent communication skills, MBA preferred.',
                'benefits' => 'Great salary package, performance bonuses, health benefits, flexible schedule, remote work options, and career growth opportunities.',
                'skills_needed' => 'Digital Marketing, SEO, SEM, Content Marketing, Analytics, Team Leadership',
                'company_name' => 'Growth Dynamics',
                'company_website' => 'https://growthdynamics.example.com',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'education_level' => 'bachelor',
                'salary_min' => 70000,
                'salary_max' => 90000,
                'salary_currency' => 'USD',
                'salary_type' => 'yearly',
                'country' => 'United States',
                'city' => 'New York',
                'is_remote' => 0,
                'is_verified_employer' => 1,
                'is_active' => 1,
                'is_promoted' => 1,
                'expires_at' => Carbon::now()->addDays(45),
            ],
            [
                'title' => 'UX/UI Designer',
                'description' => 'Join our design team to create beautiful and intuitive user experiences. You will work on web and mobile applications for our clients.',
                'responsibilities' => 'Create wireframes and prototypes, design user interfaces, conduct user research, collaborate with developers, and maintain design systems.',
                'requirements' => '2+ years of UX/UI design experience, proficiency in Figma and Adobe Creative Suite, strong portfolio, understanding of user-centered design principles.',
                'benefits' => 'Creative work environment, flexible hours, remote work options, professional development budget, great team culture.',
                'skills_needed' => 'Figma, Adobe XD, Sketch, User Research, Prototyping, Design Systems',
                'company_name' => 'Creative Studio',
                'company_website' => 'https://creativestudio.example.com',
                'work_type' => 'contract',
                'experience_level' => 'junior',
                'education_level' => 'diploma',
                'salary_min' => 25,
                'salary_max' => 35,
                'salary_currency' => 'USD',
                'salary_type' => 'hourly',
                'country' => 'Canada',
                'city' => 'Toronto',
                'is_remote' => 1,
                'is_verified_employer' => 0,
                'is_active' => 1,
                'expires_at' => Carbon::now()->addDays(60),
            ],
            [
                'title' => 'Data Scientist',
                'description' => 'We are looking for a Data Scientist to help us make data-driven decisions. You will work with large datasets and build machine learning models.',
                'responsibilities' => 'Analyze complex datasets, build predictive models, create data visualizations, work with stakeholders to understand business needs, and present findings.',
                'requirements' => '3+ years of data science experience, strong programming skills in Python/R, experience with machine learning frameworks, statistics background.',
                'benefits' => 'Excellent compensation, cutting-edge projects, remote work flexibility, comprehensive benefits, and continuous learning opportunities.',
                'skills_needed' => 'Python, R, Machine Learning, Statistics, Data Visualization, SQL, TensorFlow',
                'company_name' => 'Data Insights Co',
                'company_website' => 'https://datainsights.example.com',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'education_level' => 'master',
                'salary_min' => 110000,
                'salary_max' => 140000,
                'salary_currency' => 'USD',
                'salary_type' => 'yearly',
                'country' => 'United States',
                'city' => 'Seattle',
                'is_remote' => 1,
                'is_verified_employer' => 1,
                'is_active' => 1,
                'is_sponsored' => 1,
                'expires_at' => Carbon::now()->addDays(30),
            ],
            [
                'title' => 'Junior Web Developer',
                'description' => 'Looking for a passionate Junior Web Developer to join our development team. Great opportunity to learn and grow in a supportive environment.',
                'responsibilities' => 'Assist in web application development, write clean code, participate in code reviews, learn new technologies, and collaborate with senior developers.',
                'requirements' => '1+ year of web development experience, knowledge of HTML/CSS/JavaScript, basic understanding of backend development, eager to learn.',
                'benefits' => 'Mentorship program, learning budget, flexible hours, remote work options, health benefits, and career growth path.',
                'skills_needed' => 'HTML, CSS, JavaScript, React, Node.js, Git, Basic Backend',
                'company_name' => 'StartUp Tech',
                'company_website' => 'https://startuptech.example.com',
                'work_type' => 'full_time',
                'experience_level' => 'entry',
                'education_level' => 'diploma',
                'salary_min' => 50000,
                'salary_max' => 65000,
                'salary_currency' => 'USD',
                'salary_type' => 'yearly',
                'country' => 'United States',
                'city' => 'Austin',
                'is_remote' => 0,
                'is_verified_employer' => 0,
                'is_active' => 1,
                'expires_at' => Carbon::now()->addDays(90),
            ],
        ];

        foreach ($sampleJobs as $index => $jobData) {
            $user = $users->random();
            $category = $categories->random();
            
            $jobData['user_id'] = $user->user_id;
            $jobData['job_category_id'] = $category->id;
            $jobData['slug'] = Str::slug($jobData['title']) . '-' . $index;
            $jobData['contact_email'] = 'hr@' . parse_url($jobData['company_website'], PHP_URL_HOST);
            $jobData['created_at'] = Carbon::now()->subDays(rand(1, 30));
            $jobData['updated_at'] = $jobData['created_at'];
            Job::create($jobData);
        }

        $this->command->info('Sample jobs created successfully!');
    }
}
