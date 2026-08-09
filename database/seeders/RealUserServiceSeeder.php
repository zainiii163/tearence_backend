<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceMedia;
use App\Models\ServiceProvider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Replace demo marketplace listings with services owned by real login customers.
 */
class RealUserServiceSeeder extends Seeder
{
    public function run(): void
    {
        Service::query()
            ->where('status', 'active')
            ->whereHas('serviceProvider', function ($q) {
                $q->where('business_name', 'Worldwide Adverts Demo Provider');
            })
            ->update(['status' => 'draft', 'promotion_type' => 'standard']);

        $john = Customer::where('email', 'john.doe@example.com')->first();
        $vikas = Customer::where('email', 'vikas@worldwideadverts.info')->first()
            ?? Customer::where('email', 'vikasjain2412@gmail.com')->first();

        if (! $john && ! $vikas) {
            $this->command?->warn('RealUserServiceSeeder: no john/vikas customers found.');
            return;
        }

        $catalog = [
            [
                'owner' => $john,
                'business_name' => 'John Doe Creative Studio',
                'bio' => 'Brand identity and design services from a verified Worldwide Adverts seller.',
                'city' => 'Manchester',
                'listings' => [
                    ['logo-design', 'Startup Logo + Brand Mark (John Doe)', '3 concepts, vector files, and a mini brand board', 89, 'featured', 5, 'Manchester', 'https://images.unsplash.com/photo-1626785774573-4b7993143464?w=800&q=80'],
                    ['branding', 'Brand Refresh for Growing SMEs', 'Colours, type, and social templates updated for 2026', 399, 'featured', 10, 'Leeds', 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80'],
                    ['book-writing', 'Non-fiction Ghostwriting Starter', 'Outline + first 10k words with editorial notes', 799, 'promoted', 21, 'Edinburgh', 'https://images.unsplash.com/photo-1456513080880-7d36d38b9d9f?w=800&q=80'],
                ],
            ],
            [
                'owner' => $vikas,
                'business_name' => 'Vikas Digital Solutions',
                'bio' => 'Web, SEO and AI services delivered by an active Worldwide Adverts provider.',
                'city' => 'Birmingham',
                'listings' => [
                    ['seo', 'Google Business + Local SEO Sprint', 'Audit, GBP fixes, and a 30-day ranking plan', 199, 'featured', 7, 'Birmingham', 'https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?w=800&q=80'],
                    ['web-development', 'Conversion Website (5-7 pages)', 'Responsive build with CMS training included', 1200, 'featured', 14, 'Bristol', 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800&q=80'],
                    ['ai-services', 'FAQ Chatbot Trained on Your Docs', 'Website widget + handoff to human support', 420, 'featured', 10, 'Reading', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=800&q=80'],
                ],
            ],
        ];

        $created = 0;
        foreach ($catalog as $block) {
            $owner = $block['owner'];
            if (! $owner) {
                continue;
            }

            $ownerId = $owner->customer_id;
            $provider = ServiceProvider::updateOrCreate(
                ['user_id' => $ownerId],
                [
                    'business_name' => $block['business_name'],
                    'bio' => $block['bio'],
                    'country' => 'United Kingdom',
                    'city' => $block['city'],
                    'is_verified' => true,
                    'rating' => 4.9,
                    'review_count' => 12,
                ]
            );

            foreach ($block['listings'] as [$slug, $title, $tagline, $price, $promo, $days, $city, $image]) {
                $category = ServiceCategory::where('slug', $slug)->where('is_active', true)->first();
                if (! $category) {
                    $this->command?->warn("Missing category slug: {$slug}");
                    continue;
                }

                $serviceSlug = Str::slug($title) . '-u' . $ownerId;
                $service = Service::updateOrCreate(
                    ['slug' => $serviceSlug],
                    [
                        'user_id' => $ownerId,
                        'service_provider_id' => $provider->id,
                        'category_id' => $category->id,
                        'title' => $title,
                        'tagline' => $tagline,
                        'description' => '<p>'.$tagline.'</p><p>Listed by '.$block['business_name'].' on Worldwide Adverts. Order securely - payment is collected before the seller starts work.</p>',
                        'whats_included' => ['Discovery call', 'Draft delivery', '2 revision rounds', 'Source files where applicable'],
                        'whats_not_included' => ['Paid ads spend', 'Third-party licences', 'Hosting fees'],
                        'requirements' => ['Brief', 'Brand assets (if any)', 'Target audience notes'],
                        'service_type' => 'freelance',
                        'starting_price' => $price,
                        'currency' => 'USD',
                        'delivery_time' => $days,
                        'availability' => ['weekdays', 'remote'],
                        'country' => 'United Kingdom',
                        'city' => $city,
                        'status' => 'active',
                        'promotion_type' => $promo,
                        'promotion_expires_at' => now()->addMonths(3),
                        'is_verified' => true,
                        'languages' => ['English'],
                        'rating' => 4.8,
                        'review_count' => 6,
                    ]
                );

                ServiceMedia::where('service_id', $service->id)->delete();
                ServiceMedia::create([
                    'service_id' => $service->id,
                    'type' => 'image',
                    'file_path' => $image,
                    'file_name' => 'cover.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 0,
                    'caption' => $title,
                    'sort_order' => 0,
                    'is_thumbnail' => true,
                ]);

                $created++;
            }
        }

        $this->command?->info("RealUserServiceSeeder: {$created} active services ready for real customers.");
    }
}
