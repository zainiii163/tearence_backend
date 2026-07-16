<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceCategory;

/**
 * IT & Computing / Fiverr-style online service categories only.
 * Offline trades (cleaning, transport, etc.) belong under Business — deactivated here.
 */
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'slug' => 'web-development',
                'description' => 'Websites, WordPress, frontend/backend development',
                'icon' => 'fas fa-code',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'App & Software',
                'slug' => 'app-software',
                'description' => 'Mobile apps, SaaS, and custom software',
                'icon' => 'fas fa-mobile-alt',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Graphic Design',
                'slug' => 'graphic-design',
                'description' => 'Logo, branding, UI/UX, and visual design',
                'icon' => 'fas fa-palette',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Digital Marketing',
                'slug' => 'digital-marketing',
                'description' => 'SEO, social media, content and growth marketing',
                'icon' => 'fas fa-chart-line',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Advertising',
                'slug' => 'advertising',
                'description' => 'PPC, Google Ads, Meta ads and campaigns',
                'icon' => 'fas fa-bullhorn',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Writing & Content',
                'slug' => 'writing-content',
                'description' => 'Copywriting, blogs, translation and content',
                'icon' => 'fas fa-pen',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Business Support',
                'slug' => 'business-support',
                'description' => 'Virtual assistants, admin and remote B2B support',
                'icon' => 'fas fa-briefcase',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'IT Consultancy',
                'slug' => 'it-consultancy',
                'description' => 'IT strategy, systems advice and tech consulting',
                'icon' => 'fas fa-user-tie',
                'sort_order' => 8,
                'is_active' => true,
            ],
        ];

        $activeSlugs = [];

        foreach ($categories as $category) {
            $activeSlugs[] = $category['slug'];
            ServiceCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Keep historical rows but hide offline / non-IT categories from Services
        ServiceCategory::whereNotIn('slug', $activeSlugs)->update(['is_active' => false]);
    }
}
