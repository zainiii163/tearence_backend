<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * Tech / IT services only — Clive: Fiverr-style, no accountants / legal / architecture / engineering.
 */
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $group = ServiceCategory::updateOrCreate(
            ['slug' => 'it-computing'],
            [
                'parent_id' => null,
                'name' => 'IT & Computing',
                'description' => 'Web, apps, design, marketing & IT support',
                'icon' => 'heroicon-o-computer-desktop',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $subcategories = [
            ['slug' => 'web-development', 'name' => 'Web Development', 'description' => 'Websites, WordPress, frontend/backend development', 'icon' => 'heroicon-o-code-bracket', 'sort_order' => 1],
            ['slug' => 'app-software', 'name' => 'App & Software', 'description' => 'Mobile apps, SaaS, and custom software', 'icon' => 'heroicon-o-device-phone-mobile', 'sort_order' => 2],
            ['slug' => 'graphic-design', 'name' => 'Graphic Design', 'description' => 'Logo, branding, UI/UX, and visual design', 'icon' => 'heroicon-o-paint-brush', 'sort_order' => 3],
            ['slug' => 'digital-marketing', 'name' => 'Digital Marketing', 'description' => 'SEO, social media, content and growth marketing', 'icon' => 'heroicon-o-chart-bar', 'sort_order' => 4],
            ['slug' => 'advertising', 'name' => 'Advertising', 'description' => 'PPC, Google Ads, Meta ads and campaigns', 'icon' => 'heroicon-o-megaphone', 'sort_order' => 5],
            ['slug' => 'writing-content', 'name' => 'Writing & Content', 'description' => 'Copywriting, blogs, translation and content', 'icon' => 'heroicon-o-pencil', 'sort_order' => 6],
            ['slug' => 'business-support', 'name' => 'Business Support', 'description' => 'Virtual assistants, admin and remote B2B support', 'icon' => 'heroicon-o-briefcase', 'sort_order' => 7],
            ['slug' => 'it-consultancy', 'name' => 'IT Consultancy', 'description' => 'IT strategy, systems advice and tech consulting', 'icon' => 'heroicon-o-user-circle', 'sort_order' => 8],
        ];

        $activeSlugs = ['it-computing'];

        foreach ($subcategories as $sub) {
            $activeSlugs[] = $sub['slug'];
            ServiceCategory::updateOrCreate(
                ['slug' => $sub['slug']],
                array_merge($sub, [
                    'parent_id' => $group->id,
                    'is_active' => true,
                ])
            );
        }

        // Hide accountants, legal, architecture, engineering, and any other non-tech rows
        ServiceCategory::whereNotIn('slug', $activeSlugs)->update(['is_active' => false]);
    }
}
