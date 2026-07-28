<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample live Services & Solutions listings for the marketplace browse page.
 */
class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->orderBy('user_id')->first();
        if (!$user) {
            $this->command?->warn('ServiceSeeder skipped: no users in database.');
            return;
        }

        $userId = $user->user_id ?? $user->getKey();

        $provider = ServiceProvider::firstOrCreate(
            ['user_id' => $userId],
            [
                'business_name' => 'Worldwide Adverts Demo Provider',
                'bio' => 'Demo services for the Services & Solutions marketplace',
                'country' => 'United Kingdom',
                'city' => 'London',
                'is_verified' => true,
                'rating' => 4.8,
                'review_count' => 24,
            ]
        );

        $samples = [
            ['logo-design', 'Professional Logo Design Pack', 'Modern logo + brand kit for startups', 89, 'featured'],
            ['logo-brand-identity', 'Full Brand Identity Suite', 'Logo, colours, typography and guidelines', 249, 'promoted'],
            ['wordpress-themes', 'Custom WordPress Theme Build', 'Fast, SEO-ready theme tailored to your brand', 450, 'standard'],
            ['wordpress-customization', 'WordPress Site Customization', 'Theme tweaks, WooCommerce and speed fixes', 120, 'standard'],
            ['book-writing', 'Book Ghostwriting Starter', 'Outline + first 10,000 words for your book', 799, 'featured'],
            ['book-editing', 'Manuscript Developmental Edit', 'Structure, clarity and chapter-level feedback', 350, 'promoted'],
            ['proofreading', 'Professional Proofreading', 'Final pass for typos, grammar and consistency', 99, 'standard'],
            ['graphic-design', 'Social Media Creative Pack', '15 branded posts for Instagram & Facebook', 149, 'sponsored'],
            ['branding', 'Brand Refresh Package', 'Visual system update for growing businesses', 399, 'featured'],
            ['ui-ux-design', 'Landing Page UI/UX Design', 'Wireframes + high-fidelity desktop & mobile', 299, 'promoted'],
            ['seo', 'Local SEO Growth Plan', 'Audit, on-page fixes and 30-day ranking plan', 199, 'featured'],
            ['social-media-marketing', 'Social Media Management (Monthly)', '12 posts, captions and community replies', 350, 'standard'],
            ['google-ads', 'Google Ads Campaign Setup', 'Search campaign build + conversion tracking', 275, 'promoted'],
            ['web-development', 'Business Website Development', '5–7 page responsive site with CMS', 1200, 'featured'],
            ['app-software', 'MVP Mobile App Prototype', 'Clickable prototype for iOS & Android', 1800, 'standard'],
            ['video-animation', 'Explainer Video (60s)', 'Script, voiceover and motion graphics', 550, 'sponsored'],
            ['ai-services', 'Custom Chatbot Setup', 'AI chatbot trained on your FAQs & docs', 420, 'featured'],
            ['business-support', 'Virtual Assistant (20 hrs)', 'Admin, inbox and scheduling support', 280, 'standard'],
            ['it-consultancy', 'IT Systems Review', 'Stack audit + 90-day improvement roadmap', 650, 'promoted'],
        ];

        $created = 0;
        foreach ($samples as [$slug, $title, $tagline, $price, $promo]) {
            $category = ServiceCategory::where('slug', $slug)->where('is_active', true)->first();
            if (!$category) {
                continue;
            }

            $serviceSlug = Str::slug($title);
            Service::updateOrCreate(
                ['slug' => $serviceSlug],
                [
                    'user_id' => $userId,
                    'service_provider_id' => $provider->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'tagline' => $tagline,
                    'description' => $tagline.' Delivered by verified providers on Worldwide Adverts Services & Solutions. Customise scope after enquiry.',
                    'whats_included' => ['Discovery call', 'Draft delivery', '2 revision rounds', 'Source files where applicable'],
                    'whats_not_included' => ['Paid ads spend', 'Third-party licences', 'Hosting fees'],
                    'requirements' => 'Share your brief, brand assets and deadline.',
                    'service_type' => 'freelance',
                    'starting_price' => $price,
                    'currency' => 'USD',
                    'delivery_time' => 14, // days (integer column on live)
                    'country' => 'United Kingdom',
                    'city' => 'London',
                    'status' => 'active',
                    'promotion_type' => $promo,
                    'promotion_expires_at' => now()->addMonths(3),
                    'is_verified' => true,
                    'languages' => ['English'],
                    'views' => random_int(40, 900),
                    'enquiries' => random_int(2, 80),
                    'rating' => round(mt_rand(42, 50) / 10, 1),
                    'review_count' => random_int(3, 40),
                ]
            );
            $created++;
        }

        $this->command?->info("Service listings ready: {$created} demo services");
    }
}
