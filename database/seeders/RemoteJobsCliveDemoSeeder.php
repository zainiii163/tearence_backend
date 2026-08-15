<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\User;
use App\Support\JobSchema;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Clive: demo remote jobs inspired by popular remote boards (Remotive, Dynamite Jobs,
 * Jobspresso, etc.) so admin can assess how they appear in the Jobs section.
 * These are first-party WWA listings — not outbound board scrapes.
 */
class RemoteJobsCliveDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = JobCategory::query()->get();
        $user = User::query()->orderBy('user_id')->first()
            ?? User::query()->where('user_id', '>', 0)->first();

        if (!$user) {
            $this->command?->warn('RemoteJobsCliveDemoSeeder skipped: no users.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command?->warn('RemoteJobsCliveDemoSeeder skipped: no job categories. Run JobCategoriesSeeder first.');
            return;
        }

        $pickCategory = function (string $needle) use ($categories) {
            $found = $categories->first(function ($c) use ($needle) {
                return stripos((string) $c->name, $needle) !== false;
            });
            return $found ?: $categories->first();
        };

        // Card images (office / team / workspace) so Jobs browse matches other marketplaces
        $images = [
            'https://images.unsplash.com/photo-1497366214041-512025aae3b4?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1517041899536-9a241eaceb3d?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1553877522-43269d4ea984?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=800&q=80',
            'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=800&q=80',
        ];

        $demos = [
            [
                'slug' => 'clive-demo-senior-react-engineer-remote',
                'title' => 'Senior React Engineer (Fully Remote)',
                'company_name' => 'Northline Digital',
                'category' => 'Technology',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'salary_min' => 110000,
                'salary_max' => 145000,
                'salary_type' => 'yearly',
                'is_featured' => 1,
                'image' => $images[0],
                'description' => 'Build customer-facing React apps for a global remote-first product team. TypeScript, Next.js, and GraphQL day to day.',
                'skills_needed' => 'React, TypeScript, Next.js, GraphQL, Git',
            ],
            [
                'slug' => 'clive-demo-devops-engineer-remote',
                'title' => 'Senior DevOps Engineer',
                'company_name' => 'CloudPeak Systems',
                'category' => 'Technology',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'salary_min' => 120000,
                'salary_max' => 160000,
                'salary_type' => 'yearly',
                'is_featured' => 1,
                'image' => $images[1],
                'description' => 'Own CI/CD, Kubernetes and observability for a distributed engineering org. Fully remote worldwide.',
                'skills_needed' => 'AWS, Kubernetes, Terraform, Docker, GitHub Actions',
            ],
            [
                'slug' => 'clive-demo-product-designer-remote',
                'title' => 'Product Designer (UI/UX)',
                'company_name' => 'Brightfolio',
                'category' => 'Creative',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'salary_min' => 85000,
                'salary_max' => 110000,
                'salary_type' => 'yearly',
                'is_featured' => 0,
                'image' => $images[2],
                'description' => 'Design end-to-end product flows for a SaaS dashboard. Async-friendly remote team across EU & US timezones.',
                'skills_needed' => 'Figma, Prototyping, Design Systems, User Research',
            ],
            [
                'slug' => 'clive-demo-content-marketer-remote',
                'title' => 'Content Marketer — Remote',
                'company_name' => 'SignalCraft Media',
                'category' => 'Sales',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'salary_min' => 65000,
                'salary_max' => 85000,
                'salary_type' => 'yearly',
                'is_sponsored' => 1,
                'image' => $images[3],
                'description' => 'Own blog, SEO and newsletter growth. Style similar to Jobspresso / SkipTheDrive remote marketing roles.',
                'skills_needed' => 'SEO, Content Strategy, Analytics, Copywriting',
            ],
            [
                'slug' => 'clive-demo-customer-success-remote',
                'title' => 'Customer Success Manager',
                'company_name' => 'Harbor SaaS',
                'category' => 'Customer',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'salary_min' => 70000,
                'salary_max' => 95000,
                'salary_type' => 'yearly',
                'image' => $images[4],
                'description' => 'Onboard and retain mid-market accounts. Fully remote with quarterly offsites.',
                'skills_needed' => 'CRM, Communication, Account Management, SaaS',
            ],
            [
                'slug' => 'clive-demo-data-analyst-remote',
                'title' => 'Remote Data Analyst',
                'company_name' => 'MetricNest',
                'category' => 'Technology',
                'work_type' => 'full_time',
                'experience_level' => 'junior',
                'salary_min' => 60000,
                'salary_max' => 80000,
                'salary_type' => 'yearly',
                'image' => $images[5],
                'description' => 'Turn product data into dashboards and insights. Work-from-anywhere role for assessing Jobs UI cards.',
                'skills_needed' => 'SQL, Python, Looker, Excel',
            ],
            [
                'slug' => 'clive-demo-technical-writer-remote',
                'title' => 'Technical Writer',
                'company_name' => 'Docsify Labs',
                'category' => 'Creative',
                'work_type' => 'contract',
                'experience_level' => 'mid',
                'salary_min' => 45,
                'salary_max' => 70,
                'salary_type' => 'hourly',
                'image' => $images[6],
                'description' => 'Write API docs and developer guides for a developer-tools company. Contract remote (Virtual Vocations / Remotive style).',
                'skills_needed' => 'Technical Writing, Markdown, APIs, Git',
            ],
            [
                'slug' => 'clive-demo-account-executive-remote',
                'title' => 'Account Executive — Remote Sales',
                'company_name' => 'Pipeline Orbit',
                'category' => 'Sales',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'salary_min' => 55000,
                'salary_max' => 120000,
                'salary_type' => 'yearly',
                'is_urgent' => 1,
                'image' => $images[7],
                'description' => 'Close mid-market SaaS deals. Base + commission. Fully remote sales role for Jobs section review.',
                'skills_needed' => 'B2B Sales, Salesforce, Negotiation, Prospecting',
            ],
            [
                'slug' => 'clive-demo-support-specialist-remote',
                'title' => 'Customer Support Specialist',
                'company_name' => 'Helpwave',
                'category' => 'Customer',
                'work_type' => 'full_time',
                'experience_level' => 'entry',
                'salary_min' => 40000,
                'salary_max' => 52000,
                'salary_type' => 'yearly',
                'image' => $images[8],
                'description' => 'Chat and email support for a consumer app. Overlap with US business hours; inspired by Outsourcely / CloudPeeps style roles.',
                'skills_needed' => 'Zendesk, Written English, Empathy, Troubleshooting',
            ],
            [
                'slug' => 'clive-demo-fullstack-rails-remote',
                'title' => 'Tech Lead — Full-Stack Rails',
                'company_name' => 'Mitre Media Demo',
                'category' => 'Technology',
                'work_type' => 'full_time',
                'experience_level' => 'senior',
                'salary_min' => 170000,
                'salary_max' => 200000,
                'salary_type' => 'yearly',
                'is_featured' => 1,
                'image' => $images[9],
                'description' => 'Lead a Rails + React squad. High-salary remote engineering role to stress-test featured job cards.',
                'skills_needed' => 'Ruby on Rails, React, PostgreSQL, Leadership',
            ],
            [
                'slug' => 'clive-demo-ai-engineer-contract-remote',
                'title' => 'Independent AI Engineer (Contract)',
                'company_name' => 'A.Team Style Projects',
                'category' => 'Technology',
                'work_type' => 'contract',
                'experience_level' => 'senior',
                'salary_min' => 120,
                'salary_max' => 170,
                'salary_type' => 'hourly',
                'is_sponsored' => 1,
                'image' => $images[10],
                'description' => 'Short-cycle AI product builds. Contract / freelance remote listing for Jobs UI assessment.',
                'skills_needed' => 'Python, LLMs, LangChain, FastAPI, MLOps',
            ],
            [
                'slug' => 'clive-demo-recruiter-remote',
                'title' => 'Remote Technical Recruiter',
                'company_name' => 'Talent Relay',
                'category' => 'Sales',
                'work_type' => 'full_time',
                'experience_level' => 'mid',
                'salary_min' => 75000,
                'salary_max' => 100000,
                'salary_type' => 'yearly',
                'image' => $images[11],
                'description' => 'Source engineers for remote-first clients. Pangian / EuropeRemotely style recruiting role on WWA.',
                'skills_needed' => 'Sourcing, LinkedIn Recruiter, ATS, Communication',
            ],
        ];

        $cols = JobSchema::columns();
        $created = 0;

        foreach ($demos as $index => $demo) {
            $category = $pickCategory($demo['category']);
            $host = Str::slug($demo['company_name']) . '.example.com';

            $payload = [
                'title' => $demo['title'],
                'slug' => $demo['slug'],
                'description' => $demo['description'],
                'responsibilities' => 'Deliver high-quality work asynchronously, collaborate on weekly standups, document decisions, and meet sprint goals.',
                'requirements' => 'Proven experience in a similar remote role, reliable internet, overlapping hours with the team, and strong written communication.',
                'benefits' => 'Fully remote, flexible hours, equipment stipend, health allowance where eligible, and career growth.',
                'skills_needed' => $demo['skills_needed'],
                'company_name' => $demo['company_name'],
                'company_website' => 'https://' . $host,
                'work_type' => $demo['work_type'],
                'experience_level' => $demo['experience_level'],
                'education_level' => 'bachelor',
                'salary_min' => $demo['salary_min'],
                'salary_max' => $demo['salary_max'],
                'salary_currency' => 'USD',
                'salary_type' => $demo['salary_type'],
                'country' => 'Worldwide',
                'city' => 'Remote',
                'application_method' => 'email',
                'is_active' => 1,
                'status' => 'active',
                'is_featured' => (int) ($demo['is_featured'] ?? 0),
                'is_sponsored' => (int) ($demo['is_sponsored'] ?? 0),
                'is_urgent' => (int) ($demo['is_urgent'] ?? 0),
                'is_promoted' => 0,
                'expires_at' => Carbon::now()->addDays(45),
                'user_id' => $user->user_id ?? $user->getKey(),
                $cols['category'] => $category->id,
                $cols['remote'] => true,
                $cols['verified'] => true,
                $cols['logo'] => $demo['image'],
                $cols['email'] => 'careers@' . $host,
                'created_at' => Carbon::now()->subDays(($index % 10) + 1),
                'updated_at' => Carbon::now(),
            ];

            // Prefer company_logo; also set logo_url if both columns exist
            if ($cols['logo'] === 'company_logo' && \Illuminate\Support\Facades\Schema::hasColumn('jobs', 'logo_url')) {
                $payload['logo_url'] = $demo['image'];
            }

            Job::updateOrCreate(
                ['slug' => $demo['slug']],
                JobSchema::filterPayload($payload)
            );
            $created++;
        }

        $this->command?->info("Clive remote demo jobs ready: {$created} listings (Jobs section assessment).");
    }
}
