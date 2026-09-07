<?php

namespace Database\Seeders;

use App\Models\AffiliateCategory;
use App\Models\BookAdvert;
use App\Models\BusinessAffiliateOffer;
use App\Models\CustomerBusiness;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Clive (Sep 2026): live 10% affiliate promotions with genuine links.
 *
 * - 5 real WWA books (Book Writting Ltd)
 * - 3 games from MGNIT GAMING LTD (mgnitgaming.com)
 * - MGNIT LTD services (mgnit.co.uk)
 * - Car Services Ltd — join & pay for a post → 10% for promoter
 *
 * Also wires real website / social links on the partner business profiles
 * and soft-hides placeholder .example marketplace offers.
 *
 * Run on API host:
 *   php artisan db:seed --class=ClivePartnerAffiliateOffersSeeder --force
 */
class ClivePartnerAffiliateOffersSeeder extends Seeder
{
    private const COMMISSION = 10;

    private const SITE = 'https://worldwideadverts.info';

    public function run(): void
    {
        $this->hidePlaceholderOffers();
        $this->fixPartnerBusinessLinks();

        $seller = $this->resolveSeller();
        if (! $seller) {
            $this->command?->error('No users found. Create a user first.');

            return;
        }

        $cat = function (string $slug, int $fallback): int {
            return (int) (AffiliateCategory::query()->where('slug', $slug)->value('id') ?: $fallback);
        };

        $hasJoin = Schema::hasColumn('business_affiliate_offers', 'join_instructions');
        $hasShopping = Schema::hasColumn('business_affiliate_offers', 'sale_price');

        $offers = array_merge(
            $this->bookOffers($cat),
            $this->gamingOffers($cat),
            $this->mgnitServiceOffers($cat),
            $this->carServicesOffers($cat),
        );

        $created = 0;
        $updated = 0;

        foreach ($offers as $row) {
            $payload = [
                'user_id' => $seller->user_id,
                'affiliate_category_id' => $row['affiliate_category_id'],
                'business_name' => $row['business_name'],
                'product_service_title' => $row['product_service_title'],
                'tagline' => $row['tagline'],
                'description' => $row['description'],
                'country' => 'United Kingdom',
                'region' => null,
                'commission_type' => 'percentage',
                'commission_rate' => self::COMMISSION,
                'cookie_duration' => $row['cookie_duration'] ?? 30,
                'allowed_traffic_types' => ['social_media', 'email', 'blogging', 'influencer', 'ppc'],
                'restrictions' => 'No trademark bidding. Genuine traffic only. No incentivized spam.',
                'tracking_link' => $row['tracking_link'],
                'promotional_assets' => $row['assets'] ?? [],
                'business_email' => $row['business_email'],
                'website_url' => $row['website_url'],
                'is_verified' => true,
                'status' => 'approved',
                'is_promoted' => (bool) ($row['is_promoted'] ?? true),
                'is_featured' => (bool) ($row['is_featured'] ?? true),
                'is_sponsored' => false,
                'price' => $row['price'] ?? null,
                'payment_status' => 'paid',
                'paid_at' => now()->subDay(),
                'is_active' => true,
                'views' => $row['views'] ?? 120,
                'clicks' => $row['clicks'] ?? 40,
                'applications' => $row['applications'] ?? 2,
            ];

            if ($hasJoin) {
                $payload['join_instructions'] = $row['join'];
            }

            if ($hasShopping && isset($row['sale_price'])) {
                $payload['sale_price'] = $row['sale_price'];
                $payload['compare_at_price'] = $row['compare_at_price'] ?? null;
            }

            $existing = BusinessAffiliateOffer::query()
                ->where(function ($q) use ($row) {
                    $q->where('product_service_title', $row['product_service_title'])
                        ->orWhere('tracking_link', $row['tracking_link']);
                })
                ->first();

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                BusinessAffiliateOffer::create($payload);
                $created++;
            }
        }

        $this->command?->info("Clive partner affiliate offers: created {$created}, updated {$updated} (all @ 10%).");
        $this->command?->info('Check marketplace: /affiliates/marketplace — live links only.');
    }

    private function resolveSeller(): ?User
    {
        return User::query()
            ->whereIn('email', [
                'worldwideadverts@gmail.com',
                'marketplace@worldwideadverts.info',
                'carservices365@gmail.com',
                'info@mgnit.co.uk',
            ])
            ->orderByRaw("email = 'worldwideadverts@gmail.com' desc")
            ->first()
            ?? User::query()->orderBy('user_id')->first();
    }

    private function hidePlaceholderOffers(): void
    {
        $n = BusinessAffiliateOffer::query()
            ->where(function ($q) {
                $q->where('tracking_link', 'like', '%.example%')
                    ->orWhere('website_url', 'like', '%.example%')
                    ->orWhere('tracking_link', 'like', '%example.com%')
                    ->orWhere('website_url', 'like', '%example.com%');
            })
            ->update([
                'is_active' => false,
                'status' => 'rejected',
                'is_featured' => false,
                'is_promoted' => false,
            ]);

        $this->command?->info("Soft-hid {$n} placeholder (.example) affiliate offers.");
    }

    private function fixPartnerBusinessLinks(): void
    {
        $partners = [
            [
                'names' => ['MGNIT LTD'],
                'slug' => 'mgnit-ltd',
                'website' => 'https://mgnit.co.uk',
                'email' => 'info@mgnit.co.uk',
                'booking' => 'https://mgnit.co.uk/contact/',
                'services' => [
                    'Web development',
                    'Mobile app development',
                    'Software development',
                    'Logo & graphics design',
                    'Web hosting & domains',
                ],
                'links' => [
                    ['platform' => 'website', 'label' => 'mgnit.co.uk', 'url' => 'https://mgnit.co.uk'],
                    ['platform' => 'custom', 'label' => 'Services', 'url' => 'https://mgnit.co.uk/services/'],
                    ['platform' => 'custom', 'label' => 'Web development', 'url' => 'https://mgnit.co.uk/web-development/'],
                    ['platform' => 'custom', 'label' => 'App development', 'url' => 'https://mgnit.co.uk/app-development/'],
                ],
            ],
            [
                'names' => ['MGNIT GAMING LTD', 'MGNIT Gaming'],
                'slug' => 'mgnit-gaming-ltd',
                'website' => 'https://mgnitgaming.com/',
                'email' => 'info@mgnitgaming.com',
                'booking' => 'https://mgnitgaming.com/',
                'services' => ['Free online games', 'Arcade', 'Puzzles', 'Action'],
                'links' => [
                    ['platform' => 'website', 'label' => 'mgnitgaming.com', 'url' => 'https://mgnitgaming.com/'],
                    ['platform' => 'custom', 'label' => 'Play games', 'url' => 'https://mgnitgaming.com/'],
                    [
                        'platform' => 'facebook',
                        'label' => 'Facebook',
                        'url' => 'https://www.facebook.com/profile.php?id=61558615070188',
                    ],
                ],
            ],
            [
                'names' => ['Book Writting Ltd', 'Book Writing Ltd'],
                'slug' => 'book-writting-ltd',
                'website' => 'https://bookwritting.com/',
                'email' => 'info@bookwritting.com',
                'booking' => 'https://bookwritting.com/',
                'services' => ['Ebooks', 'Audiobooks', 'Courses', 'Writing & publishing'],
                'links' => [
                    ['platform' => 'website', 'label' => 'bookwritting.com', 'url' => 'https://bookwritting.com/'],
                    ['platform' => 'custom', 'label' => 'WWA Books', 'url' => self::SITE.'/books'],
                ],
            ],
            [
                'names' => ['Car Services Ltd'],
                'slug' => 'car-services-ltd',
                'website' => 'https://carservicesltd.com/',
                'email' => 'info@carservicesltd.com',
                'booking' => 'https://carservicesltd.com/',
                'services' => [
                    'Vehicle adverts',
                    'Mechanics & garages',
                    'Car hire & sale',
                    'Tow & recovery',
                ],
                'links' => [
                    ['platform' => 'website', 'label' => 'carservicesltd.com', 'url' => 'https://carservicesltd.com/'],
                    ['platform' => 'custom', 'label' => 'Post an advert', 'url' => 'https://carservicesltd.com/'],
                    ['platform' => 'custom', 'label' => 'Mechanics hub', 'url' => 'https://carservicesltd.com/mechanics-hub'],
                ],
            ],
        ];

        foreach ($partners as $partner) {
            $biz = CustomerBusiness::query()
                ->where(function ($q) use ($partner) {
                    foreach ($partner['names'] as $name) {
                        $q->orWhere('business_name', $name);
                    }
                })
                ->first();

            if (! $biz) {
                $this->command?->warn('Business not found: '.$partner['names'][0]);

                continue;
            }

            $profile = is_array($biz->category_profile) ? $biz->category_profile : [];
            $profile['social_links'] = $partner['links'];
            $profile['booking_url'] = $partner['booking'];
            $profile['services'] = $partner['services'];
            if (empty($profile['opening_hours'])) {
                $profile['opening_hours'] = [
                    'monday' => '09:00 – 18:00',
                    'tuesday' => '09:00 – 18:00',
                    'wednesday' => '09:00 – 18:00',
                    'thursday' => '09:00 – 18:00',
                    'friday' => '09:00 – 18:00',
                    'saturday' => '10:00 – 16:00',
                    'sunday' => 'Closed',
                ];
            }

            $desiredSlug = $partner['slug'];
            $slugTaken = CustomerBusiness::query()
                ->where('slug', $desiredSlug)
                ->where('id', '!=', $biz->id)
                ->exists();
            if ($slugTaken) {
                $desiredSlug = $biz->slug ?: $desiredSlug;
            }

            $biz->fill([
                'slug' => $desiredSlug,
                'business_website' => $partner['website'],
                'booking_url' => $partner['booking'],
                'business_email' => $biz->business_email ?: $partner['email'],
                'category_profile' => $profile,
                'status' => 'active',
            ])->save();

            $this->command?->info(
                'Updated links for '.$biz->business_name.' → /business/'.$desiredSlug.' (id '.$biz->id.').'
            );
        }
    }

    private function bookOffers(callable $cat): array
    {
        $picked = [
            ['slug' => '5-expensive-home-buying-mistakes', 'title' => '5 Expensive Home Buying Mistakes'],
            ['slug' => '470-crock-pot-recipes', 'title' => '470 Crock Pot Recipes'],
            ['slug' => '4-step-profit-system', 'title' => '4 Step Profit System'],
            ['slug' => '45-baby-nursery-decorating-ideas', 'title' => '45 Baby Nursery Decorating Ideas'],
            ['slug' => '99-windows-xp-performance-tips', 'title' => '99 Windows Xp Performance Tips'],
        ];

        $img = 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1200&q=80';
        $out = [];

        foreach ($picked as $book) {
            $row = null;
            if (class_exists(BookAdvert::class) && Schema::hasTable('books_adverts')) {
                $row = BookAdvert::query()
                    ->where(function ($q) use ($book) {
                        $q->where('slug', $book['slug'])
                            ->orWhere('title', $book['title']);
                    })
                    ->first();
            }

            $title = $row?->title ?: $book['title'];
            $slug = $row?->slug ?: $book['slug'];
            $track = self::SITE.'/books/'.$slug;
            $cover = $row?->cover_image
                ?? $row?->main_image
                ?? $img;

            $out[] = [
                'affiliate_category_id' => $cat('education-courses', 6),
                'business_name' => 'Book Writting Ltd',
                'product_service_title' => 'Book: '.$title,
                'tagline' => 'Promote this live WWA book listing — 10% commission',
                'description' => "Affiliate promotion for \"{$title}\" on Worldwide Adverts Books.\n\nShare your hop link. You earn 10% when a referred visitor purchases or converts on this book listing within the cookie window.\n\nGenuine destination: {$track}",
                'tracking_link' => $track,
                'website_url' => 'https://bookwritting.com/',
                'business_email' => 'info@bookwritting.com',
                'price' => $row?->price !== null ? (float) $row->price : 0,
                'assets' => [is_string($cover) && str_starts_with($cover, 'http') ? $cover : $img],
                'join' => 'Promote on book blogs, newsletters, and social. Cookie 30 days. 10% of paid conversion.',
                'is_featured' => true,
                'is_promoted' => true,
            ];
        }

        return $out;
    }

    private function gamingOffers(callable $cat): array
    {
        $games = [
            [
                'title' => 'Titans Defense Run',
                'path' => '/game/titans-defense-run',
                'blurb' => 'Arcade parkour + strategic attacks — play free on MGNIT Gaming.',
                'img' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=1200&q=80',
            ],
            [
                'title' => 'Dragon Ball Jigsaw Puzzle',
                'path' => '/game/dragon-ball-jigsaw-puzzle',
                'blurb' => 'Online jigsaw puzzle — play free on MGNIT Gaming.',
                'img' => 'https://images.unsplash.com/photo-1611195979584-dc9bfc8f3f8f?w=1200&q=80',
            ],
            [
                'title' => 'Flying Bubbles',
                'path' => '/game/flying-bubbles',
                'blurb' => 'Aim, shoot, and clear bubbles — arcade classic on MGNIT Gaming.',
                'img' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=1200&q=80',
            ],
        ];

        $out = [];
        foreach ($games as $game) {
            $url = 'https://mgnitgaming.com'.$game['path'];
            $out[] = [
                'affiliate_category_id' => $cat('technology-gadgets', 1),
                'business_name' => 'MGNIT GAMING LTD',
                'product_service_title' => 'Game: '.$game['title'],
                'tagline' => 'Promote this live MGNIT Gaming title — 10% commission',
                'description' => "{$game['blurb']}\n\nDrive players to the live game page. You earn 10% when referred users convert (premium / sponsored placements / partner campaigns) within the cookie window.\n\nGenuine link: {$url}",
                'tracking_link' => $url,
                'website_url' => 'https://mgnitgaming.com/',
                'business_email' => 'info@mgnitgaming.com',
                'price' => 0,
                'assets' => [$game['img']],
                'join' => 'Share on gaming communities and Shorts/Reels. Cookie 30 days. 10% commission.',
                'is_featured' => true,
                'is_promoted' => true,
            ];
        }

        return $out;
    }

    private function mgnitServiceOffers(callable $cat): array
    {
        $services = [
            [
                'title' => 'MGNIT — Web Development',
                'url' => 'https://mgnit.co.uk/web-development/',
                'tagline' => 'UK web development packages — promote & earn 10%',
                'desc' => 'Promote MGNIT web development for UK businesses. Commission when a referred client books a paid package.',
                'price' => 450,
            ],
            [
                'title' => 'MGNIT — App Development',
                'url' => 'https://mgnit.co.uk/app-development/',
                'tagline' => 'iOS & Android app builds — promote & earn 10%',
                'desc' => 'Promote MGNIT mobile app development. Earn 10% when a referred business starts a paid app project.',
                'price' => 2500,
            ],
            [
                'title' => 'MGNIT — Digital Services Hub',
                'url' => 'https://mgnit.co.uk/services/',
                'tagline' => 'Full MGNIT services catalogue — 10% affiliate',
                'desc' => 'Send leads to the live MGNIT services page (web, apps, design, hosting). 10% on paid service conversions.',
                'price' => 450,
            ],
        ];

        $img = 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80';
        $out = [];

        foreach ($services as $svc) {
            $out[] = [
                'affiliate_category_id' => $cat('business-services', 12),
                'business_name' => 'MGNIT LTD',
                'product_service_title' => $svc['title'],
                'tagline' => $svc['tagline'],
                'description' => $svc['desc']."\n\nGenuine link: {$svc['url']}",
                'tracking_link' => $svc['url'],
                'website_url' => 'https://mgnit.co.uk',
                'business_email' => 'info@mgnit.co.uk',
                'price' => $svc['price'],
                'assets' => [$img],
                'join' => 'LinkedIn, local business groups, and Google Ads (no brand bidding). Cookie 45 days. 10%.',
                'cookie_duration' => 45,
                'is_featured' => true,
                'is_promoted' => true,
            ];
        }

        return $out;
    }

    private function carServicesOffers(callable $cat): array
    {
        return [
            [
                'affiliate_category_id' => $cat('automotive', 8),
                'business_name' => 'Car Services Ltd',
                'product_service_title' => 'CarServicesLTD — Join & pay for a vehicle post',
                'tagline' => '10% when a referred customer joins and pays for a post',
                'description' => "Promote carservicesltd.com. When a customer you referred creates an account and pays for a vehicle / service post, you earn 10% commission.\n\nGenuine platform: https://carservicesltd.com/\n\nIdeal for automotive creators, garage partners, and local Facebook groups.",
                'tracking_link' => 'https://carservicesltd.com/',
                'website_url' => 'https://carservicesltd.com/',
                'business_email' => 'info@carservicesltd.com',
                'price' => 29,
                'assets' => ['https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=80'],
                'join' => 'Share signup / post-an-advert flows. Commission locks after paid post clears. Cookie 30 days. Flat 10%.',
                'is_featured' => true,
                'is_promoted' => true,
            ],
            [
                'affiliate_category_id' => $cat('automotive', 8),
                'business_name' => 'Car Services Ltd',
                'product_service_title' => 'CarServicesLTD — Mechanics & garage hub',
                'tagline' => 'Promote mechanics listings — 10% on paid posts',
                'description' => "Send drivers and garage owners to the live Mechanics hub on CarServicesLTD. Earn 10% when a referred customer pays to post.\n\nGenuine link: https://carservicesltd.com/mechanics-hub",
                'tracking_link' => 'https://carservicesltd.com/mechanics-hub',
                'website_url' => 'https://carservicesltd.com/',
                'business_email' => 'info@carservicesltd.com',
                'price' => 29,
                'assets' => ['https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=1200&q=80'],
                'join' => 'Local SEO content and garage partnerships. Cookie 30 days. 10%.',
                'is_featured' => true,
                'is_promoted' => false,
            ],
        ];
    }
}
