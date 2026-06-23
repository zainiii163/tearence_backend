<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Technology & IT',
                'slug' => 'technology-it',
                'description' => 'Software development, IT support, and technology roles',
                'icon' => '💻',
                'color' => '#3B82F6',
                'is_active' => true,
                'sort_order' => 1,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Healthcare & Medical',
                'slug' => 'healthcare-medical',
                'description' => 'Doctors, nurses, and healthcare professionals',
                'icon' => '🏥',
                'color' => '#EF4444',
                'is_active' => true,
                'sort_order' => 2,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Sales & Marketing',
                'slug' => 'sales-marketing',
                'description' => 'Sales representatives, marketing specialists',
                'icon' => '📈',
                'color' => '#10B981',
                'is_active' => true,
                'sort_order' => 3,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Finance & Accounting',
                'slug' => 'finance-accounting',
                'description' => 'Financial analysts, accountants, bookkeepers',
                'icon' => '💰',
                'color' => '#F59E0B',
                'is_active' => true,
                'sort_order' => 4,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Engineering & Construction',
                'slug' => 'engineering-construction',
                'description' => 'Civil engineers, architects, construction workers',
                'icon' => '🏗️',
                'color' => '#6366F1',
                'is_active' => true,
                'sort_order' => 5,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Education & Training',
                'slug' => 'education-training',
                'description' => 'Teachers, trainers, educational consultants',
                'icon' => '🎓',
                'color' => '#8B5CF6',
                'is_active' => true,
                'sort_order' => 6,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Creative & Media',
                'slug' => 'creative-media',
                'description' => 'Designers, writers, content creators',
                'icon' => '🎨',
                'color' => '#EC4899',
                'is_active' => true,
                'sort_order' => 7,
                'jobs_count' => 0,
            ],
            [
                'name' => 'Customer Service',
                'slug' => 'customer-service',
                'description' => 'Support representatives, call center agents',
                'icon' => '🎧',
                'color' => '#14B8A6',
                'is_active' => true,
                'sort_order' => 8,
                'jobs_count' => 0,
            ],
        ];

        foreach ($categories as $category) {
            JobCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
