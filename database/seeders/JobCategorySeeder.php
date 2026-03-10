<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobCategory;
use Illuminate\Support\Facades\DB;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing categories
        DB::table('job_categories')->delete();

        $categories = [
            [
                'name' => 'Technology & IT',
                'slug' => 'technology-it',
                'description' => 'Software development, IT support, cybersecurity, and technology-related roles',
                'icon' => 'laptop-code',
                'color' => '#3B82F6',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Healthcare & Medical',
                'slug' => 'healthcare-medical',
                'description' => 'Doctors, nurses, medical technicians, and healthcare administration',
                'icon' => 'heartbeat',
                'color' => '#EF4444',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Sales & Marketing',
                'slug' => 'sales-marketing',
                'description' => 'Sales representatives, marketing specialists, and business development',
                'icon' => 'chart-line',
                'color' => '#10B981',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Finance & Accounting',
                'slug' => 'finance-accounting',
                'description' => 'Accountants, financial analysts, bookkeepers, and finance managers',
                'icon' => 'dollar-sign',
                'color' => '#F59E0B',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Engineering & Construction',
                'slug' => 'engineering-construction',
                'description' => 'Civil engineers, architects, construction workers, and project managers',
                'icon' => 'hard-hat',
                'color' => '#6B7280',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Hospitality & Tourism',
                'slug' => 'hospitality-tourism',
                'description' => 'Hotel staff, tour guides, restaurant workers, and travel agents',
                'icon' => 'plane',
                'color' => '#8B5CF6',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Retail & Customer Service',
                'slug' => 'retail-customer-service',
                'description' => 'Retail associates, customer service representatives, and store managers',
                'icon' => 'shopping-cart',
                'color' => '#EC4899',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Logistics & Transport',
                'slug' => 'logistics-transport',
                'description' => 'Truck drivers, warehouse workers, supply chain managers, and delivery personnel',
                'icon' => 'truck',
                'color' => '#14B8A6',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'Education & Training',
                'slug' => 'education-training',
                'description' => 'Teachers, trainers, tutors, and education administrators',
                'icon' => 'graduation-cap',
                'color' => '#F97316',
                'sort_order' => 9,
                'is_active' => true,
            ],
            [
                'name' => 'Creative & Media',
                'slug' => 'creative-media',
                'description' => 'Designers, writers, photographers, videographers, and content creators',
                'icon' => 'palette',
                'color' => '#A855F7',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Remote Jobs',
                'slug' => 'remote-jobs',
                'description' => 'Jobs that can be done remotely from anywhere',
                'icon' => 'home',
                'color' => '#06B6D4',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'name' => 'Part-Time & Freelance',
                'slug' => 'part-time-freelance',
                'description' => 'Part-time positions, freelance work, and gig economy jobs',
                'icon' => 'clock',
                'color' => '#84CC16',
                'sort_order' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            JobCategory::create($category);
        }

        $this->command->info('Job categories seeded successfully!');
    }
}
