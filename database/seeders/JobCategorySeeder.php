<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Upsert categories (do not wipe existing production data)

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
            [
                'name' => 'Science',
                'slug' => 'science',
                'description' => 'Research, laboratory, and science roles',
                'icon' => 'flask',
                'color' => '#0EA5E9',
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'name' => 'Agriculture',
                'slug' => 'agriculture',
                'description' => 'Farming, agribusiness, and agricultural roles',
                'icon' => 'seedling',
                'color' => '#65A30D',
                'sort_order' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Professional services and specialist roles',
                'icon' => 'briefcase',
                'color' => '#475569',
                'sort_order' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Consultancy',
                'slug' => 'consultancy',
                'description' => 'Consulting and advisory roles',
                'icon' => 'comments',
                'color' => '#7C3AED',
                'sort_order' => 16,
                'is_active' => true,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Other job categories',
                'icon' => 'ellipsis-h',
                'color' => '#94A3B8',
                'sort_order' => 17,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            JobCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info('Job categories seeded successfully!');
    }
}
