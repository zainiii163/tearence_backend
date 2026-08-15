<?php

namespace Database\Seeders;

use App\Models\Affiliate;
use App\Models\AffiliateApplication;
use App\Models\AffiliateCategory;
use App\Models\AffiliateHopConversion;
use App\Models\BusinessAffiliateOffer;
use App\Models\User;
use App\Models\UserAffiliatePost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Live affiliate hub data on real Worldwide Adverts pages.
 *
 * Covers: Marketplace (sales / drops / codes), Courses, Affiliate Ads,
 * hops, promoter earnings, seller programs.
 *
 * Run on the API host after migrate:
 *   php artisan db:seed --class=AffiliateLiveHubSeeder --force
 */
class AffiliateLiveHubSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureCategories();

        $seller = $this->resolveSeller();
        $promoter = $this->resolvePromoter($seller);
        if (! $seller) {
            $this->command?->error('No users found. Create a user first.');
            return;
        }

        $offers = $this->upsertOffers($seller);
        $this->upsertPaidAds();
        $apps = $this->upsertApplications($promoter, $offers);
        $this->upsertUserPosts($promoter, $apps, $offers);
        $this->upsertConversions($apps, $offers);

        $this->command?->info('Affiliate live hub seeded.');
        $this->command?->info('Seller (Marketplace owner): '.$seller->email);
        $this->command?->info('Promoter (hops / ads / earnings): '.$promoter->email);
        $this->command?->info('Check: /affiliates  /affiliates/marketplace  /affiliates/courses');
    }

    private function ensureCategories(): void
    {
        $rows = [
            ['Technology & Gadgets', 'technology-gadgets', 1],
            ['Fashion & Beauty', 'fashion-beauty', 2],
            ['Travel & Tourism', 'travel-tourism', 3],
            ['Finance & Insurance', 'finance-insurance', 4],
            ['Health & Wellness', 'health-wellness', 5],
            ['Education & Courses', 'education-courses', 6],
            ['Home & Garden', 'home-garden', 7],
            ['Automotive', 'automotive', 8],
            ['Real Estate', 'real-estate', 9],
            ['Software & SaaS', 'software-saas', 10],
            ['Food & Lifestyle', 'food-lifestyle', 11],
            ['Business Services', 'business-services', 12],
        ];

        foreach ($rows as [$name, $slug, $sort]) {
            AffiliateCategory::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $name.' affiliate programs on Worldwide Adverts.',
                    'is_active' => true,
                    'sort_order' => $sort,
                ]
            );
        }
    }

    private function resolveSeller(): ?User
    {
        return User::query()
            ->whereIn('email', [
                'worldwideadverts@gmail.com',
                'marketplace@worldwideadverts.info',
            ])
            ->orderByRaw("email = 'worldwideadverts@gmail.com' desc")
            ->first()
            ?? User::query()->orderBy('user_id')->first();
    }

    private function resolvePromoter(User $seller): User
    {
        $promoter = User::query()
            ->where('user_id', '!=', $seller->user_id)
            ->where(function ($q) {
                $q->where('email', 'promoter@worldwideadverts.info')
                    ->orWhere('email', 'like', '%vikas%')
                    ->orWhere('email', 'hanzoali96@gmail.com');
            })
            ->first();

        if ($promoter) {
            return $promoter;
        }

        $other = User::query()->where('user_id', '!=', $seller->user_id)->orderBy('user_id')->first();
        if ($other) {
            return $other;
        }

        return User::query()->firstOrCreate(
            ['email' => 'promoter@worldwideadverts.info'],
            [
                'user_uid' => Str::random(13),
                'first_name' => 'Affiliate',
                'last_name' => 'Promoter',
                'password' => Hash::make('WwaPromoter2026'),
                'email_verified_at' => now(),
                'timezone' => 'UTC',
            ]
        );
    }

    private function catId(string $slug, int $fallback): int
    {
        return (int) (AffiliateCategory::query()->where('slug', $slug)->value('id') ?: $fallback);
    }

    private function upsertOffers(User $seller): array
    {
        $hasShopping = Schema::hasColumn('business_affiliate_offers', 'sale_price');
        $hasJoin = Schema::hasColumn('business_affiliate_offers', 'join_instructions');
        $site = 'https://worldwideadverts.info';

        $catalog = [
            [
                'title' => 'Worldwide Adverts — Books marketplace',
                'business' => 'Worldwide Adverts',
                'tagline' => 'Promote books listed on Worldwide Adverts and earn on sales',
                'description' => "Send readers to the live Books marketplace on Worldwide Adverts.\n\nYou earn commission when a referred visitor buys a book listing within the cookie window. Use the hop on Affiliate Ads, newsletters, or book review channels.",
                'slug' => 'education-courses',
                'country' => 'United Kingdom',
                'commission_type' => 'percentage',
                'commission_rate' => 15,
                'cookie' => 30,
                'track' => $site.'/books',
                'sale' => 12.00,
                'compare' => 18.00,
                'code' => 'READ15',
                'promo' => 'percent_off',
                'label' => '15% off starter titles',
                'drop' => null,
                'featured' => true,
                'verified' => true,
                'promoted' => true,
                'image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1200&q=80',
            ],
            [
                'title' => 'Affiliate marketing starter course',
                'business' => 'Worldwide Adverts Academy',
                'tagline' => 'Learn hop links, cookies, and commissions — then join Marketplace',
                'description' => "A practical starter course for new promoters on Worldwide Adverts.\n\nCovers: join a Marketplace offer, copy your hop, post it as an Affiliate Ad, and read earnings. Promote this course to beginners; you earn when they buy the paid seat.",
                'slug' => 'education-courses',
                'country' => 'United Kingdom',
                'commission_type' => 'fixed',
                'commission_rate' => 25,
                'cookie' => 45,
                'track' => $site.'/affiliates/courses',
                'sale' => 49.00,
                'compare' => 79.00,
                'code' => 'START25',
                'promo' => 'sale',
                'label' => 'Sale — £49',
                'drop' => null,
                'featured' => true,
                'verified' => true,
                'promoted' => false,
                'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=1200&q=80',
            ],
            [
                'title' => 'Worldwide Adverts — Jobs board',
                'business' => 'Worldwide Adverts',
                'tagline' => 'Promote live vacancies and earn when employers convert',
                'description' => "Drive job seekers and hiring managers to the Worldwide Adverts jobs board.\n\nCommission is paid when a referred visitor purchases a paid job listing or featured vacancy package within the cookie window.",
                'slug' => 'business-services',
                'country' => 'United Kingdom',
                'commission_type' => 'percentage',
                'commission_rate' => 20,
                'cookie' => 30,
                'track' => $site.'/jobs',
                'sale' => 29.00,
                'compare' => 49.00,
                'code' => null,
                'promo' => 'price_drop',
                'label' => 'Price drop',
                'drop' => null,
                'featured' => true,
                'verified' => true,
                'promoted' => false,
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200&q=80',
            ],
            [
                'title' => 'Services marketplace — hire specialists',
                'business' => 'Worldwide Adverts',
                'tagline' => 'Web, SEO, design and trade services listed worldwide',
                'description' => "Promote the Services hub. You earn when a referred client books or pays a service listing.\n\nStrong for LinkedIn, local Facebook groups, and YouTube how-to videos.",
                'slug' => 'business-services',
                'country' => 'United Kingdom',
                'commission_type' => 'percentage',
                'commission_rate' => 18,
                'cookie' => 21,
                'track' => $site.'/services',
                'sale' => 99.00,
                'compare' => 149.00,
                'code' => 'HIRE18',
                'promo' => 'amount_off',
                'label' => '£50 off first booking',
                'drop' => null,
                'featured' => false,
                'verified' => true,
                'promoted' => true,
                'image' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80',
            ],
            [
                'title' => 'Property listings worldwide',
                'business' => 'Worldwide Adverts Property',
                'tagline' => 'Homes, rentals and land — promote the live property board',
                'description' => "Send buyers and tenants to live property listings on Worldwide Adverts.\n\nEarn when a referred visitor pays for a featured or sponsored property advert.",
                'slug' => 'real-estate',
                'country' => 'United Arab Emirates',
                'commission_type' => 'percentage',
                'commission_rate' => 12,
                'cookie' => 14,
                'track' => $site.'/property',
                'sale' => 39.00,
                'compare' => null,
                'code' => null,
                'promo' => 'none',
                'label' => null,
                'drop' => null,
                'featured' => true,
                'verified' => true,
                'promoted' => false,
                'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&q=80',
            ],
            [
                'title' => 'Vehicles marketplace',
                'business' => 'Worldwide Adverts Motors',
                'tagline' => 'Cars, bikes and commercial vehicles listed worldwide',
                'description' => "Promote the Vehicles board. Commission on paid dealer / featured vehicle listings attributed through your hop.",
                'slug' => 'automotive',
                'country' => 'United Kingdom',
                'commission_type' => 'fixed',
                'commission_rate' => 15,
                'cookie' => 14,
                'track' => $site.'/vehicles',
                'sale' => 25.00,
                'compare' => 40.00,
                'code' => null,
                'promo' => 'sale',
                'label' => 'Listing sale',
                'drop' => null,
                'featured' => false,
                'verified' => true,
                'promoted' => false,
                'image' => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?w=1200&q=80',
            ],
            [
                'title' => 'Online stores on Worldwide Adverts',
                'business' => 'Worldwide Adverts Stores',
                'tagline' => 'Open a storefront — dropping this week for new sellers',
                'description' => "Promote storefronts on Worldwide Adverts. Affiliates can tag this offer before the drop goes live.\n\nYou earn when a referred seller pays to open or feature a store.",
                'slug' => 'software-saas',
                'country' => 'United Kingdom',
                'commission_type' => 'percentage',
                'commission_rate' => 25,
                'cookie' => 30,
                'track' => $site.'/stores',
                'sale' => 79.00,
                'compare' => 129.00,
                'code' => 'STORE25',
                'promo' => 'product_drop',
                'label' => 'Dropping soon',
                'drop' => now()->addDays(4)->setTime(10, 0),
                'featured' => true,
                'verified' => true,
                'promoted' => true,
                'image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=1200&q=80',
            ],
            [
                'title' => 'Funding & crowdfund campaigns',
                'business' => 'Worldwide Adverts Funding',
                'tagline' => 'Promote live funding campaigns and earn on paid boosts',
                'description' => "Send backers to live funding campaigns. Commission when a referred visitor pays for campaign promotion or a featured listing.",
                'slug' => 'finance-insurance',
                'country' => 'United States',
                'commission_type' => 'percentage',
                'commission_rate' => 10,
                'cookie' => 7,
                'track' => $site.'/funding',
                'sale' => 20.00,
                'compare' => null,
                'code' => null,
                'promo' => 'none',
                'label' => null,
                'drop' => null,
                'featured' => false,
                'verified' => true,
                'promoted' => false,
                'image' => 'https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?w=1200&q=80',
            ],
            [
                'title' => 'Health & wellness listings',
                'business' => 'Worldwide Adverts Lifestyle',
                'tagline' => 'Fitness, supplements and clinic services — on sale this week',
                'description' => "Promote health and wellness listings across Worldwide Adverts. Cookie 45 days. No medical cure claims in your ads.",
                'slug' => 'health-wellness',
                'country' => 'United States',
                'commission_type' => 'percentage',
                'commission_rate' => 22,
                'cookie' => 45,
                'track' => $site.'/services',
                'sale' => 39.00,
                'compare' => 59.00,
                'code' => 'WELL22',
                'promo' => 'percent_off',
                'label' => '22% off',
                'drop' => null,
                'featured' => true,
                'verified' => true,
                'promoted' => false,
                'image' => 'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=1200&q=80',
            ],
            [
                'title' => 'Travel stays & experiences',
                'business' => 'Worldwide Adverts Travel',
                'tagline' => 'Resorts, venues and city stays listed on the network',
                'description' => "Promote travel and venue listings. You earn when a referred guest or venue owner pays for a featured stay listing.",
                'slug' => 'travel-tourism',
                'country' => 'United Arab Emirates',
                'commission_type' => 'percentage',
                'commission_rate' => 8,
                'cookie' => 7,
                'track' => $site,
                'sale' => 89.00,
                'compare' => 120.00,
                'code' => null,
                'promo' => 'price_drop',
                'label' => 'Price drop',
                'drop' => null,
                'featured' => false,
                'verified' => true,
                'promoted' => true,
                'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1200&q=80',
            ],
        ];

        $map = [];
        foreach ($catalog as $row) {
            $payload = [
                'user_id' => $seller->user_id,
                'affiliate_category_id' => $this->catId($row['slug'], 6),
                'business_name' => $row['business'],
                'product_service_title' => $row['title'],
                'tagline' => $row['tagline'],
                'description' => $row['description'],
                'country' => $row['country'],
                'region' => null,
                'commission_type' => $row['commission_type'],
                'commission_rate' => $row['commission_rate'],
                'cookie_duration' => $row['cookie'],
                'allowed_traffic_types' => ['social_media', 'email', 'blogging', 'influencer', 'ppc'],
                'restrictions' => 'No trademark bidding on Worldwide Adverts. No fake scarcity. Hop links only.',
                'tracking_link' => $row['track'],
                'promotional_assets' => [$row['image']],
                'business_email' => 'affiliates@worldwideadverts.info',
                'website_url' => $site,
                'is_verified' => (bool) $row['verified'],
                'status' => 'approved',
                'is_promoted' => (bool) $row['promoted'],
                'is_featured' => (bool) $row['featured'],
                'is_sponsored' => false,
                'price' => 20,
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(2),
                'expires_at' => now()->addDays(90),
                'is_active' => true,
                'views' => random_int(80, 900),
                'clicks' => random_int(20, 300),
                'applications' => 0,
            ];

            if ($hasJoin) {
                $payload['join_instructions'] = 'Get your hop on this offer, then use Post as Affiliate Ad. Payouts from $25 via Dashboard → Affiliates → Earnings.';
            }
            if ($hasShopping) {
                $payload['sale_price'] = $row['sale'];
                $payload['compare_at_price'] = $row['compare'];
                $payload['discount_code'] = $row['code'];
                $payload['promotion_type'] = $row['promo'];
                $payload['promotion_label'] = $row['label'];
                $payload['drop_at'] = $row['drop'];
            }

            $offer = BusinessAffiliateOffer::query()
                ->where('product_service_title', $row['title'])
                ->first();

            if ($offer) {
                $offer->update($payload);
            } else {
                $offer = BusinessAffiliateOffer::create($payload);
            }

            $map[$row['title']] = $offer;
        }

        return $map;
    }

    private function upsertPaidAds(): void
    {
        $site = 'https://worldwideadverts.info';
        $ads = [
            [
                'position' => 'header',
                'title' => 'Worldwide Adverts — Marketplace',
                'link' => $site.'/affiliates/marketplace',
                'image_url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80',
            ],
            [
                'position' => 'sidebar',
                'title' => 'Live jobs on Worldwide Adverts',
                'link' => $site.'/jobs',
                'image_url' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=1200&q=80',
            ],
            [
                'position' => 'footer',
                'title' => 'Books marketplace',
                'link' => $site.'/books',
                'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1200&q=80',
            ],
            [
                'position' => 'header',
                'title' => 'Affiliate starter courses',
                'link' => $site.'/affiliates/courses',
                'image_url' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=1200&q=80',
            ],
        ];

        $hide = ['status' => 'inactive'];
        if (Schema::hasColumn('affiliate_links', 'is_active')) {
            $hide['is_active'] = false;
        }
        Affiliate::query()
            ->where(function ($q) {
                $q->where('link', 'like', '%example.com%')
                    ->orWhere('title', 'like', 'Partner Link%');
            })
            ->update($hide);

        foreach ($ads as $ad) {
            $payload = [
                'position' => $ad['position'],
                'link' => $ad['link'],
                'title' => $ad['title'],
                'status' => 'active',
            ];
            if (Schema::hasColumn('affiliate_links', 'image_url')) {
                $payload['image_url'] = $ad['image_url'];
            }
            if (Schema::hasColumn('affiliate_links', 'price')) {
                $payload['price'] = 10;
            }
            if (Schema::hasColumn('affiliate_links', 'payment_status')) {
                $payload['payment_status'] = 'paid';
                $payload['paid_at'] = now()->subDay();
                $payload['expires_at'] = now()->addDays(30);
            }
            if (Schema::hasColumn('affiliate_links', 'is_active')) {
                $payload['is_active'] = true;
            }

            Affiliate::query()->updateOrCreate(
                ['link' => $ad['link'], 'title' => $ad['title']],
                $payload
            );
        }
    }

    private function upsertApplications(User $promoter, array $offers): array
    {
        $apps = [];
        foreach ($offers as $title => $offer) {
            if ((int) $offer->user_id === (int) $promoter->user_id) {
                continue;
            }

            $app = AffiliateApplication::query()->firstOrNew([
                'business_affiliate_offer_id' => $offer->id,
                'user_id' => $promoter->user_id,
            ]);
            $app->fill([
                'message' => 'Promoting this Worldwide Adverts offer on Affiliate Ads and social.',
                'promotion_methods' => ['social_media', 'blogging'],
                'status' => 'approved',
                'approval_notes' => 'Live hub seed',
                'joined_at' => now()->subDays(1),
            ]);
            $app->save();
            $app->ensureTrackingCode();
            $apps[$title] = $app->fresh();
        }

        foreach ($offers as $offer) {
            $offer->update([
                'applications' => AffiliateApplication::query()
                    ->where('business_affiliate_offer_id', $offer->id)
                    ->count(),
            ]);
        }

        return $apps;
    }

    private function upsertUserPosts(User $promoter, array $apps, array $offers): void
    {
        $posts = [
            [
                'offer' => 'Affiliate marketing starter course',
                'title' => 'Start affiliate marketing on Worldwide Adverts',
                'description' => 'Join the starter course, get a hop link, and post it here. Cookie window applies — you earn when readers buy.',
                'hashtags' => ['Affiliate', 'WorldwideAdverts', 'HopLink'],
                'audience' => 'New promoters and side-hustle readers',
            ],
            [
                'offer' => 'Worldwide Adverts — Books marketplace',
                'title' => 'Books on sale — promote with your hop',
                'description' => 'Live book listings on Worldwide Adverts. Use your Marketplace hop so sales in the cookie window pay you.',
                'hashtags' => ['Books', 'Reading', 'Affiliate'],
                'audience' => 'Readers and book reviewers',
            ],
            [
                'offer' => 'Worldwide Adverts — Jobs board',
                'title' => 'Hiring? Jobs board is on a price drop',
                'description' => 'Share the jobs hop. Featured vacancy packages convert inside a 30-day cookie.',
                'hashtags' => ['Jobs', 'Hiring', 'RemoteWork'],
                'audience' => 'Job seekers and hiring managers',
            ],
            [
                'offer' => 'Online stores on Worldwide Adverts',
                'title' => 'Storefronts dropping soon — tag this offer now',
                'description' => 'Product drop this week. Join the Marketplace offer first, then share your hop before it goes live.',
                'hashtags' => ['Ecommerce', 'Stores', 'ProductDrop'],
                'audience' => 'Sellers opening an online store',
            ],
        ];

        foreach ($posts as $row) {
            $offer = $offers[$row['offer']] ?? null;
            $app = $apps[$row['offer']] ?? null;
            $hop = $app?->hop_url ?: ($offer?->tracking_link ?: 'https://worldwideadverts.info/affiliates');
            $image = is_array($offer?->promotional_assets) ? ($offer->promotional_assets[0] ?? '') : '';
            if ($image === '') {
                $image = 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=1200&q=80';
            }

            $payload = [
                'user_id' => $promoter->user_id,
                'affiliate_category_id' => $offer?->affiliate_category_id ?: $this->catId('education-courses', 6),
                'title' => $row['title'],
                'description' => $row['description'],
                'country' => $offer?->country ?: 'United Kingdom',
                'affiliate_link' => $hop,
                'image' => $image,
                'hashtags' => $row['hashtags'],
                'target_audience' => $row['audience'],
                'status' => 'approved',
                'is_active' => true,
                'payment_status' => 'paid',
                'paid_at' => now()->subDay(),
                'expires_at' => now()->addDays(14),
                'views' => random_int(40, 200),
                'clicks' => random_int(8, 60),
            ];

            UserAffiliatePost::query()->updateOrCreate(
                ['user_id' => $promoter->user_id, 'title' => $row['title']],
                $payload
            );
        }
    }

    private function upsertConversions(array $apps, array $offers): void
    {
        if (! Schema::hasTable('affiliate_hop_conversions')) {
            return;
        }

        $pairs = [
            ['Affiliate marketing starter course', 49.00, 'WWA-LIVE-COURSE-1'],
            ['Worldwide Adverts — Books marketplace', 18.00, 'WWA-LIVE-BOOK-1'],
            ['Worldwide Adverts — Jobs board', 29.00, 'WWA-LIVE-JOB-1'],
        ];

        foreach ($pairs as [$title, $sale, $orderId]) {
            $app = $apps[$title] ?? null;
            $offer = $offers[$title] ?? null;
            if (! $app || ! $offer || ! $app->tracking_code) {
                continue;
            }

            $commission = $offer->commission_type === 'fixed'
                ? (float) $offer->commission_rate
                : round($sale * ((float) $offer->commission_rate) / 100, 2);

            AffiliateHopConversion::query()->updateOrCreate(
                [
                    'business_affiliate_offer_id' => $offer->id,
                    'order_id' => $orderId,
                ],
                [
                    'affiliate_application_id' => $app->id,
                    'tracking_code' => $app->tracking_code,
                    'sale_amount' => $sale,
                    'commission_amount' => $commission,
                    'commission_type' => $offer->commission_type,
                    'commission_rate' => $offer->commission_rate,
                    'status' => 'confirmed',
                    'attributed_via' => 'code',
                ]
            );

            $app->forceFill([
                'conversions_count' => AffiliateHopConversion::query()
                    ->where('affiliate_application_id', $app->id)
                    ->count(),
                'earnings_total' => (float) AffiliateHopConversion::query()
                    ->where('affiliate_application_id', $app->id)
                    ->sum('commission_amount'),
                'clicks_count' => max((int) $app->clicks_count, 12),
            ])->save();
        }
    }
}
