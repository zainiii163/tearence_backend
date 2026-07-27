<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * Services & Solutions categories — matches frontend SERVICE_MAIN_CATEGORIES
 * (Logo Design, WordPress, Book Writing, Graphic Design, etc.).
 */
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            [
                'slug' => 'logo-design',
                'name' => 'Logo Design',
                'icon' => '✨',
                'description' => 'Logo, brand marks and identity packs',
                'children' => [
                    ['slug' => 'logo-brand-identity', 'name' => 'Brand Identity', 'icon' => '🏷️'],
                    ['slug' => 'logo-icon-design', 'name' => 'Icon Design', 'icon' => '◆'],
                    ['slug' => 'logo-redesign', 'name' => 'Logo Redesign', 'icon' => '♻️'],
                ],
            ],
            [
                'slug' => 'wordpress',
                'name' => 'WordPress',
                'icon' => '🔌',
                'description' => 'Themes, plugins and WordPress customization',
                'children' => [
                    ['slug' => 'wordpress-themes', 'name' => 'Themes', 'icon' => '🧩'],
                    ['slug' => 'wordpress-plugins', 'name' => 'Plugins', 'icon' => '🔌'],
                    ['slug' => 'wordpress-customization', 'name' => 'Customization', 'icon' => '⚙️'],
                ],
            ],
            [
                'slug' => 'book-writing',
                'name' => 'Book Writing',
                'icon' => '📚',
                'description' => 'Writing, editing, proofreading and book services',
                'children' => [
                    ['slug' => 'book-editing', 'name' => 'Editing', 'icon' => '📝'],
                    ['slug' => 'proofreading', 'name' => 'Proofreading', 'icon' => '✅'],
                    ['slug' => 'ghostwriting', 'name' => 'Ghostwriting', 'icon' => '👻'],
                    ['slug' => 'book-formatting', 'name' => 'Formatting', 'icon' => '📖'],
                    ['slug' => 'book-cover-design', 'name' => 'Book Cover Design', 'icon' => '🎨'],
                ],
            ],
            [
                'slug' => 'graphic-design',
                'name' => 'Graphic Design',
                'icon' => '🎨',
                'description' => 'Branding, illustration, UI/UX and print',
                'children' => [
                    ['slug' => 'branding', 'name' => 'Branding', 'icon' => '🏷️'],
                    ['slug' => 'illustration', 'name' => 'Illustration', 'icon' => '🖌️'],
                    ['slug' => 'ui-ux-design', 'name' => 'UI/UX Design', 'icon' => '📐'],
                    ['slug' => 'print-design', 'name' => 'Print Design', 'icon' => '🖨️'],
                ],
            ],
            [
                'slug' => 'digital-marketing',
                'name' => 'Digital Marketing',
                'icon' => '📈',
                'description' => 'SEO, social, email and content marketing',
                'children' => [
                    ['slug' => 'seo', 'name' => 'SEO', 'icon' => '🔍'],
                    ['slug' => 'social-media-marketing', 'name' => 'Social Media Marketing', 'icon' => '📱'],
                    ['slug' => 'email-marketing', 'name' => 'Email Marketing', 'icon' => '✉️'],
                    ['slug' => 'content-marketing', 'name' => 'Content Marketing', 'icon' => '📰'],
                ],
            ],
            [
                'slug' => 'advertising',
                'name' => 'Advertising',
                'icon' => '📣',
                'description' => 'Google Ads, social ads and PPC',
                'children' => [
                    ['slug' => 'google-ads', 'name' => 'Google Ads', 'icon' => '🔎'],
                    ['slug' => 'social-ads', 'name' => 'Social Ads', 'icon' => '📣'],
                    ['slug' => 'ppc', 'name' => 'PPC Campaigns', 'icon' => '💰'],
                ],
            ],
            [
                'slug' => 'web-development',
                'name' => 'Web Development',
                'icon' => '🌐',
                'description' => 'Websites, frontend and backend development',
                'children' => [],
            ],
            [
                'slug' => 'app-software',
                'name' => 'App & Software',
                'icon' => '📱',
                'description' => 'Mobile apps, SaaS and custom software',
                'children' => [],
            ],
            [
                'slug' => 'video-animation',
                'name' => 'Video & Animation',
                'icon' => '🎬',
                'description' => 'Video editing, motion graphics and animation',
                'children' => [],
            ],
            [
                'slug' => 'ai-services',
                'name' => 'AI Services',
                'icon' => '🤖',
                'description' => 'AI, chatbots, automation and ML',
                'children' => [],
            ],
            [
                'slug' => 'business-support',
                'name' => 'Business Support',
                'icon' => '💼',
                'description' => 'Virtual assistants, admin and B2B support',
                'children' => [],
            ],
            [
                'slug' => 'it-consultancy',
                'name' => 'IT Consultancy',
                'icon' => '🧭',
                'description' => 'IT strategy, systems advice and consulting',
                'children' => [],
            ],
        ];

        $activeSlugs = [];
        $sort = 1;

        foreach ($tree as $main) {
            $activeSlugs[] = $main['slug'];
            $parent = ServiceCategory::updateOrCreate(
                ['slug' => $main['slug']],
                [
                    'parent_id' => null,
                    'name' => $main['name'],
                    'description' => $main['description'] ?? null,
                    'icon' => $main['icon'] ?? null,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]
            );

            $childSort = 1;
            foreach ($main['children'] as $child) {
                $activeSlugs[] = $child['slug'];
                ServiceCategory::updateOrCreate(
                    ['slug' => $child['slug']],
                    [
                        'parent_id' => $parent->id,
                        'name' => $child['name'],
                        'description' => $child['description'] ?? null,
                        'icon' => $child['icon'] ?? $main['icon'] ?? null,
                        'sort_order' => $childSort++,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Retire old flat IT tree / non-matching rows (keep history, hide from browse)
        ServiceCategory::whereNotIn('slug', $activeSlugs)->update(['is_active' => false]);

        $this->command?->info('Service categories synced: '.count($activeSlugs).' active slugs');
    }
}
