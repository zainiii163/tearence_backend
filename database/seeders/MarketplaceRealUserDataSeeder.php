<?php

namespace Database\Seeders;

use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\Customer;
use App\Models\CustomerStore;
use App\Models\Currency;
use App\Models\EventsVenuesAdvert;
use App\Models\EventsVenuesCategory;
use App\Models\FeaturedAdvert;
use App\Models\FundingProject;
use App\Models\Job;
use App\Models\JobCategory;
use App\Models\Listing;
use App\Models\PromotedAdvert;
use App\Models\PromotedAdvertCategory;
use App\Models\Property;
use App\Models\SponsoredAdvert;
use App\Models\SponsoredCategory;
use App\Support\JobSchema;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Seed marketplace content owned by real Customer accounts (API sellers).
 *
 * Run:
 *   php artisan db:seed --class=MarketplaceRealUserDataSeeder --force
 *
 * Logins (password: password123):
 *   john.doe@example.com
 *   jane.smith@example.com
 *   michael.johnson@example.com
 *   sarah.williams@example.com
 *   david.brown@example.com
 */
class MarketplaceRealUserDataSeeder extends Seeder
{
    public function run(): void
    {
        $customers = $this->ensureCustomers();
        if ($customers->isEmpty()) {
            $this->command?->error('MarketplaceRealUserDataSeeder: could not create customers.');
            return;
        }

        $john = $customers->get('john.doe@example.com');
        $jane = $customers->get('jane.smith@example.com');
        $michael = $customers->get('michael.johnson@example.com');
        $sarah = $customers->get('sarah.williams@example.com');
        $david = $customers->get('david.brown@example.com');

        $this->seedBuySell($john, $jane, $michael);
        $this->seedJobs($sarah, $david, $john);
        $this->seedProperties($jane, $michael, $sarah);
        $this->seedStoresAndProducts($john, $jane, $michael, $sarah, $david);
        $this->seedFeatured($john, $jane, $michael);
        $this->seedSponsored($sarah, $david, $jane);
        $this->seedPromoted($michael, $john, $sarah);
        $this->seedFunding($john, $jane, $david);
        $this->seedEventsVenues($michael, $sarah, $jane);

        if (class_exists(RealUserServiceSeeder::class)) {
            $this->call(RealUserServiceSeeder::class);
        }

        $this->command?->info('MarketplaceRealUserDataSeeder: real customer marketplace data ready.');
        $this->command?->info('Seller logins use password: password123');
    }

    protected function ensureCustomers()
    {
        $currencyId = Currency::query()->where('code', 'USD')->value('currency_id');

        $defs = [
            ['John', 'Doe', 'john.doe@example.com', '+44-7700-900101', 'Manchester'],
            ['Jane', 'Smith', 'jane.smith@example.com', '+44-7700-900102', 'London'],
            ['Michael', 'Johnson', 'michael.johnson@example.com', '+1-305-555-0103', 'Miami'],
            ['Sarah', 'Williams', 'sarah.williams@example.com', '+1-416-555-0104', 'Toronto'],
            ['David', 'Brown', 'david.brown@example.com', '+971-50-555-0105', 'Dubai'],
        ];

        $map = collect();
        foreach ($defs as [$first, $last, $email, $phone, $city]) {
            $payload = [
                'first_name' => $first,
                'last_name' => $last,
                'password_hash' => Hash::make('password123'),
                'affiliated_members' => 0,
            ];
            if (Schema::hasColumn('customer', 'customer_uid')) {
                $payload['customer_uid'] = Str::random(10);
            }
            if ($currencyId && Schema::hasColumn('customer', 'currency_id')) {
                $payload['currency_id'] = $currencyId;
            }
            if (Schema::hasColumn('customer', 'mobile_number')) {
                $payload['mobile_number'] = $phone;
            }
            if (Schema::hasColumn('customer', 'phone')) {
                $payload['phone'] = $phone;
            }
            if (Schema::hasColumn('customer', 'city')) {
                $payload['city'] = $city;
            }
            if (Schema::hasColumn('customer', 'email_verified_at')) {
                $payload['email_verified_at'] = now();
            }

            $customer = Customer::firstOrCreate(['email' => $email], $payload);
            $map->put($email, $customer);
        }

        return $map;
    }

    protected function sellerName(Customer $c): string
    {
        return trim(($c->first_name ?? '').' '.($c->last_name ?? '')) ?: ($c->email ?? 'Seller');
    }

    protected function img(string $id, int $w = 1200): string
    {
        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$w}&q=80";
    }

    protected function onlyExistingColumns(string $table, array $payload): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }
        $filtered = [];
        foreach ($payload as $key => $value) {
            if (Schema::hasColumn($table, $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    protected function seedBuySell(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('buysell_adverts')) {
            return;
        }

        $categoryId = BuySellCategory::query()->value('id');
        if (! $categoryId) {
            $this->command?->warn('Buy & Sell skipped: no categories. Run BuySellCategorySeeder first.');
            return;
        }

        $rows = [
            [$a, 'iPhone 14 Pro Max 256GB — Like New', 899, 'USA', 'New York', true, true, false, '1523201196772-8684859de552'],
            [$a, 'Leather Sofa — Mid-century Style', 650, 'UK', 'Manchester', true, false, false, '1555041469-a586c61ea9bc'],
            [$b, 'Canon EOS R6 Mark II Kit', 1890, 'UK', 'London', false, true, false, '1516035069371-29a1b244cc32'],
            [$b, 'Animals and Pets — Premium Dog Crate', 120, 'Canada', 'Toronto', false, false, true, '1587300003388-59208cc962f0'],
            [$c, 'Mountain Bike — Carbon Frame', 780, 'USA', 'Denver', true, true, false, '1485965120187-cf692dd03298'],
            [$c, 'Wireless Noise-Cancel Headphones', 129, 'USA', 'Miami', false, true, false, '1505740420928-5e560c06d30e'],
        ];

        foreach ($rows as [$owner, $title, $price, $country, $city, $featured, $promoted, $sponsored, $photo]) {
            if (! $owner) {
                continue;
            }
            BuySellAdvert::updateOrCreate(
                ['title' => $title, 'user_id' => $owner->customer_id],
                [
                    'description' => $title.' Listed by a verified Worldwide Adverts seller. Contact via email or phone.',
                    'category_id' => $categoryId,
                    'condition' => 'good',
                    'price' => $price,
                    'currency' => 'USD',
                    'negotiable' => true,
                    'country' => $country,
                    'city' => $city,
                    'seller_name' => $this->sellerName($owner),
                    'seller_email' => $owner->email,
                    'seller_phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
                    'show_phone' => true,
                    'preferred_contact' => 'email',
                    'images' => [$this->img($photo)],
                    'status' => 'active',
                    'featured' => $featured,
                    'is_promoted' => $promoted,
                    'is_sponsored' => $sponsored,
                    'verified_seller' => true,
                    'promotion_plan' => $featured || $promoted || $sponsored ? 'paid' : 'standard',
                    'promotion_status' => 'active',
                    'expires_at' => now()->addDays(60),
                ]
            );
        }
    }

    protected function seedJobs(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('jobs')) {
            return;
        }

        $categoryId = JobCategory::query()->value('id');
        $rows = [
            [$a, 'Senior Frontend Developer', 'Brightpath Digital', 'London', 'United Kingdom', '65000', '85000', true, true],
            [$a, 'Product Manager — Marketplace', 'Harbour Commerce', 'Singapore', 'Singapore', '110000', '140000', true, false],
            [$b, 'Customer Success Lead', 'Orbit Retail', 'Toronto', 'Canada', '75000', '95000', false, true],
            [$b, 'UX Designer', 'Canvas Labs', 'Berlin', 'Germany', '55000', '70000', true, false],
            [$c, 'B2B Sales Executive', 'Summit Ads', 'Nairobi', 'Kenya', '30000', '45000', false, false],
            [$c, 'Operations Coordinator', 'Northwind Logistics', 'Dubai', 'UAE', '36000', '48000', false, true],
        ];

        foreach ($rows as [$owner, $title, $company, $city, $country, $min, $max, $featured, $sponsored]) {
            if (! $owner) {
                continue;
            }
            $payload = [
                'user_id' => $owner->customer_id,
                'title' => $title,
                'slug' => Str::slug($title).'-c'.$owner->customer_id,
                'description' => $title.' at '.$company.'. Posted by a Worldwide Adverts employer account.',
                'company_name' => $company,
                'city' => $city,
                'country' => $country,
                'location' => $city.', '.$country,
                'work_type' => 'full_time',
                'salary_min' => $min,
                'salary_max' => $max,
                'salary_currency' => 'USD',
                'status' => 'active',
                'is_active' => true,
                'is_featured' => $featured,
                'featured' => $featured,
                'is_sponsored' => $sponsored,
                'sponsored' => $sponsored,
                'is_remote' => true,
                'remote_available' => true,
                'contact_email' => $owner->email,
                'application_email' => $owner->email,
                'company_logo' => $this->img('1560179707-f14ee911789f', 200),
                'logo_url' => $this->img('1560179707-f14ee911789f', 200),
                'is_verified_employer' => true,
            ];
            if ($categoryId) {
                $payload[JobSchema::column('category')] = $categoryId;
            }
            Job::updateOrCreate(
                ['slug' => $payload['slug']],
                JobSchema::filterPayload($payload)
            );
        }
    }

    protected function seedProperties(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('properties')) {
            return;
        }

        $rows = [
            [$a, 'Marina Bay 2-Bed Condo', 'Singapore', 'Singapore', 1250000, 'SGD', 'buy', 'residential', true],
            [$a, 'Bright Loft — Lisbon Centre', 'Lisbon', 'Portugal', 420000, 'EUR', 'buy', 'residential', true],
            [$b, 'Waterfront Apartment — Dubai Marina', 'Dubai', 'UAE', 185000, 'USD', 'rent', 'residential', false],
            [$b, 'Serviced Office Suite — Manchester', 'Manchester', 'United Kingdom', 285000, 'GBP', 'lease', 'commercial', false],
            [$c, 'Hillside Villa — Cape Town', 'Cape Town', 'South Africa', 780000, 'USD', 'buy', 'luxury', true],
            [$c, 'Residential Plot — Nairobi', 'Nairobi', 'Kenya', 95000, 'USD', 'invest', 'land', false],
        ];

        foreach ($rows as [$owner, $title, $city, $country, $price, $currency, $category, $type, $featured]) {
            if (! $owner) {
                continue;
            }
            $slug = Str::slug($title).'-c'.$owner->customer_id;
            $payload = [
                'title' => $title,
                'user_id' => $owner->customer_id,
                'slug' => $slug,
                'tagline' => 'Listed by '.$this->sellerName($owner),
                'category' => $category,
                'property_type' => $type,
                'country' => $country,
                'city' => $city,
                'price' => $price,
                'currency' => $currency,
                'cover_image' => $this->img('1545324418-cc1a3fa10c00'),
                'description' => $title.'. Contact the verified seller on Worldwide Adverts.',
                'active' => true,
                'approved' => true,
                'is_featured' => $featured,
                'featured' => $featured,
                'advert_type' => $featured ? 'featured' : 'standard',
                'status' => 'active',
                'seller_name' => $this->sellerName($owner),
                'seller_email' => $owner->email,
                'seller_phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
                'contact_name' => $this->sellerName($owner),
                'contact_email' => $owner->email,
                'contact_phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
            ];
            $filtered = [];
            foreach ($payload as $key => $value) {
                if (Schema::hasColumn('properties', $key)) {
                    $filtered[$key] = $value;
                }
            }
            Property::updateOrCreate(
                ['title' => $title, 'user_id' => $owner->customer_id],
                $filtered
            );
        }
    }

    protected function seedStoresAndProducts(...$owners): void
    {
        if (! Schema::hasTable('customer_store')) {
            return;
        }

        $storeDefs = [
            [0, 'wwa-atelier', 'WWA Atelier', 'Atelier Goods Ltd', 'home', '1441986300917-64674bd600d8', 'London, United Kingdom'],
            [1, 'nordic-lane', 'Nordic Lane', 'Nordic Lane Commerce', 'fashion', '1483985988355-763728e1935b', 'Stockholm, Sweden'],
            [2, 'circuit-lab', 'Circuit Lab', 'Circuit Lab Electronics', 'electronics', '1518770660439-4636190af475', 'Austin, USA'],
            [3, 'bloom-beauty-co', 'Bloom Beauty Co', 'Bloom Beauty', 'beauty', '1596462502278-27bfdc403348', 'Toronto, Canada'],
            [4, 'peak-trail-gear', 'Peak Trail Gear', 'Peak Trail Outfitters', 'sports', '1551632811-561732d1e891', 'Denver, USA'],
        ];

        $productDefs = [
            'wwa-atelier' => [
                ['Hand-loom throw', 48, '1584100936595-c0654b55a2e6'],
                ['Ceramic pour-over set', 36, '1493106641515-6ad53afa4dc6'],
                ['Walnut desk tray', 62, '1592078615290-033ee584e267'],
            ],
            'nordic-lane' => [
                ['Merino wool overshirt', 118, '1521572163474-6864f9cf17ab'],
                ['Soft linen shirt', 72, '1596755094514-f87e34085b2c'],
            ],
            'circuit-lab' => [
                ['Desk Lamp Pro', 89, '1507473885765-e6ed057f782c'],
                ['7-in-1 wireless hub', 79, '1516035069371-29a1b244cc32'],
            ],
            'bloom-beauty-co' => [
                ['Vitamin C morning serum', 38, '1570194065650-d99fb4b8ccb0'],
                ['Clay mask duo', 44, '1556228720-195a672e8a03'],
            ],
            'peak-trail-gear' => [
                ['28L daypack', 96, '1504280390367-361c6d9a1b6c'],
                ['Insulated trail bottle', 34, '1602143407151-7111542de6e8'],
            ],
        ];

        foreach ($storeDefs as [$idx, $slug, $name, $company, $category, $photo, $address]) {
            $owner = $owners[$idx] ?? null;
            if (! $owner) {
                continue;
            }

            $storePayload = [
                'customer_id' => $owner->customer_id,
                'slug' => $slug,
                'store_name' => $name,
                'company_name' => $company,
                'company_no' => 'COMP-'.strtoupper(substr($slug, 0, 4)).'-'.$owner->customer_id,
                'vat' => 'VAT-'.$owner->customer_id,
                'status' => 'active',
            ];
            if (Schema::hasColumn('customer_store', 'description')) {
                $storePayload['description'] = $name.' — independent storefront on Worldwide Adverts.';
            }
            if (Schema::hasColumn('customer_store', 'store_address')) {
                $storePayload['store_address'] = $address;
            }
            if (Schema::hasColumn('customer_store', 'category')) {
                $storePayload['category'] = $category;
            }
            if (Schema::hasColumn('customer_store', 'store_logo')) {
                $storePayload['store_logo'] = $this->img($photo, 800);
            }
            if (Schema::hasColumn('customer_store', 'is_featured')) {
                $storePayload['is_featured'] = true;
            }

            CustomerStore::updateOrCreate(
                ['slug' => $slug],
                $storePayload
            );

            if (! Schema::hasTable('listing') || empty($productDefs[$slug])) {
                continue;
            }

            foreach ($productDefs[$slug] as [$title, $price, $imgId]) {
                $productSlug = Str::slug($title).'-'.$slug;
                Listing::updateOrCreate(
                    ['slug' => $productSlug],
                    [
                        'customer_id' => $owner->customer_id,
                        'title' => $title,
                        'description' => $title.' from '.$name.'.',
                        'display_name' => $this->sellerName($owner),
                        'price' => $price,
                        'type' => 'product',
                        'status' => 'active',
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                        'is_store' => true,
                        'is_featured' => true,
                        'is_paid' => true,
                        'post_type' => 'store',
                        'attachments' => [
                            ['url' => $this->img($imgId, 800), 'type' => 'image'],
                        ],
                        'country' => explode(',', $address)[1] ?? 'Worldwide',
                        'contact_email' => $owner->email,
                    ]
                );
            }
        }
    }

    protected function seedFeatured(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('featured_adverts')) {
            return;
        }

        $rows = [
            [$a, 'Marina Bay Condo with City Views', 'Singapore', 'Singapore', 1250000, 'SGD', 'property', '1545324418-cc1a3fa10c00'],
            [$b, 'Porsche Cayenne Coupe — 2023', 'Munich', 'Germany', 98000, 'EUR', 'vehicles', '1503376780353-7e6692767b70'],
            [$c, '49" Ultrawide Curved Monitor', 'Seattle', 'USA', 899, 'USD', 'electronics', '1527443224154-c4a3942d3acf'],
            [$a, 'Brand Identity Pack — Freelance', 'Manchester', 'United Kingdom', 299, 'USD', 'services', '1626785774573-4b7993143464'],
        ];

        foreach ($rows as [$owner, $title, $city, $country, $price, $currency, $type, $photo]) {
            if (! $owner) {
                continue;
            }
            $slug = Str::slug($title).'-ft'.$owner->customer_id;
            FeaturedAdvert::updateOrCreate(
                ['slug' => $slug],
                [
                    'customer_id' => $owner->customer_id,
                    'title' => $title,
                    'description' => $title.'. Featured placement paid by '.$this->sellerName($owner).'.',
                    'price' => $price,
                    'currency' => $currency,
                    'advert_type' => $type,
                    'images' => [$this->img($photo)],
                    'contact_name' => $this->sellerName($owner),
                    'contact_email' => $owner->email,
                    'contact_phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
                    'country' => $country,
                    'city' => $city,
                    'upsell_tier' => 'featured',
                    'upsell_price' => 30,
                    'payment_status' => 'paid',
                    'starts_at' => now()->subDay(),
                    'expires_at' => now()->addDays(14),
                    'is_active' => true,
                    'is_verified_seller' => true,
                    'view_count' => rand(800, 5000),
                ]
            );
        }
    }

    protected function seedSponsored(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('sponsored_adverts')) {
            return;
        }

        $categoryId = SponsoredCategory::query()->value('id');
        $rows = [
            [$a, 'Waterfront 2-Bed Apartment — Dubai Marina', 'Dubai', 'UAE', 185000, 'property', '1502672260266-1c1ef2d93688'],
            [$b, '2021 BMW 320d M Sport — Low Mileage', 'Manchester', 'UK', 24900, 'vehicles', '1555215695-3004980ad54e'],
            [$c, 'Established Digital Agency for Sale', 'Austin', 'USA', 320000, 'business', '1497366216548-37526070297c'],
            [$a, 'Remote Product Designer Vacancy', 'Toronto', 'Canada', 0, 'jobs', '1521737711867-e3b97375f902'],
        ];

        foreach ($rows as [$owner, $title, $city, $country, $price, $type, $photo]) {
            if (! $owner) {
                continue;
            }
            $slug = Str::slug($title).'-sp'.$owner->customer_id;
            SponsoredAdvert::updateOrCreate(
                ['slug' => $slug],
                $this->onlyExistingColumns('sponsored_adverts', [
                    'title' => $title,
                    'tagline' => 'Sponsored by '.$this->sellerName($owner),
                    'description' => $title,
                    'advert_type' => ucfirst($type),
                    'category_id' => $categoryId,
                    'country' => $country,
                    'city' => $city,
                    'price' => $price,
                    'currency' => 'USD',
                    'main_image' => $this->img($photo),
                    'seller_name' => $this->sellerName($owner),
                    'business_name' => $this->sellerName($owner).' Trading',
                    'phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
                    'email' => $owner->email,
                    'verified_seller' => true,
                    'sponsorship_tier' => 'premium',
                    'sponsorship_price' => 100,
                    'payment_status' => 'paid',
                    'sponsorship_start_date' => Carbon::now()->subDays(1),
                    'sponsorship_end_date' => Carbon::now()->addDays(30),
                    'is_active' => true,
                    'is_featured' => true,
                    'created_by' => $owner->customer_id,
                    'user_id' => $owner->customer_id,
                ])
            );
        }
    }

    protected function seedPromoted(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('promoted_adverts')) {
            return;
        }

        $categoryId = PromotedAdvertCategory::query()->value('id');
        $rows = [
            [$a, 'Premium Wireless Earbuds — Noise Cancelling', 'Los Angeles', 'USA', 129, 'electronics', '1590658268037-6bf12165a8df'],
            [$b, 'Range Rover Sport HSE — 2022', 'Birmingham', 'UK', 68500, 'vehicles', '1606664515524-ed2f786a0bd6'],
            [$c, 'Bright Loft Apartment — Lisbon Centre', 'Lisbon', 'Portugal', 420000, 'property', '1493809842364-78817add7ffb'],
        ];

        foreach ($rows as [$owner, $title, $city, $country, $price, $type, $photo]) {
            if (! $owner) {
                continue;
            }
            $slug = Str::slug($title).'-pr'.$owner->customer_id;
            $advert = PromotedAdvert::query()
                ->where('title', $title)
                ->where('user_id', $owner->customer_id)
                ->first()
                ?? PromotedAdvert::query()->where('slug', $slug)->first()
                ?? new PromotedAdvert();

            $advert->fill($this->onlyExistingColumns('promoted_adverts', [
                'slug' => $slug,
                'title' => $title,
                'description' => $title.'. Promoted listing from '.$this->sellerName($owner).'.',
                'advert_type' => $type,
                'category_id' => $categoryId,
                'country' => $country,
                'city' => $city,
                'price' => $price,
                'currency' => 'USD',
                'main_image' => $this->img($photo),
                'seller_name' => $this->sellerName($owner),
                'business_name' => $this->sellerName($owner),
                'email' => $owner->email,
                'phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
                'verified_seller' => true,
                'promotion_tier' => 'promoted_premium',
                'promotion_price' => 50,
                'promotion_start' => now()->subDay(),
                'promotion_end' => now()->addDays(21),
                'payment_status' => 'paid',
                'status' => 'active',
                'is_active' => true,
                'user_id' => $owner->customer_id,
            ]));
            $advert->save();
        }
    }

    protected function seedFunding(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        $table = (new FundingProject)->getTable();
        if (! Schema::hasTable($table)) {
            return;
        }

        $rows = [
            [$a, 'Eco-Friendly Water Bottle', 'eco-friendly-water-bottle-user', 'environment', 50000, 32500, 245, true],
            [$b, 'Smart Garden System', 'smart-garden-system-user', 'technology', 75000, 41200, 318, true],
            [$c, 'Community Art Space', 'community-art-space-user', 'creative_arts', 40000, 18600, 152, false],
        ];

        foreach ($rows as [$owner, $title, $slug, $category, $goal, $funded, $backers, $featured]) {
            if (! $owner) {
                continue;
            }

            $payload = [
                'customer_id' => $owner->customer_id,
                'title' => $title,
                'slug' => $slug,
                'tagline' => 'Campaign by '.$this->sellerName($owner),
                'project_type' => 'startup',
                'category' => $category,
                'description' => $title.' crowdfunding campaign on Worldwide Adverts.',
                'problem_solved' => 'Solves a real customer need with a clear go-to-market plan.',
                'vision_mission' => 'Build a sustainable product with community support.',
                'why_matters_now' => 'Demand is rising and early backers get preferred pricing.',
                'funding_goal' => $goal,
                'current_funded' => $funded,
                'backers_count' => $backers,
                'minimum_contribution' => 10,
                'funding_model' => 'reward',
                'status' => 'active',
                'is_verified' => true,
                'is_featured' => $featured,
                'is_promoted' => ! $featured,
                'is_sponsored' => false,
                'country' => 'United States',
                'cover_image' => $this->img('1602143407151-7111542de6e8'),
                'funding_deadline' => now()->addDays(30),
                'published_at' => now()->subDays(5),
                'risk_level' => 'low',
                'use_of_funds' => json_encode(['Product' => 50, 'Marketing' => 30, 'Ops' => 20]),
                'team_members' => json_encode([['name' => $this->sellerName($owner), 'role' => 'Founder']]),
            ];

            $filtered = $this->onlyExistingColumns($table, $payload);

            $unique = Schema::hasColumn($table, 'slug')
                ? ['slug' => $slug]
                : ['title' => $title, 'customer_id' => $owner->customer_id];

            FundingProject::updateOrCreate($unique, $filtered);
        }
    }

    protected function seedEventsVenues(?Customer $a, ?Customer $b, ?Customer $c): void
    {
        if (! Schema::hasTable('events_venues_adverts')) {
            return;
        }

        $categoryId = EventsVenuesCategory::query()->value('id');
        $rows = [
            [$a, 'event', 'Global Creators Summit 2026', 'London', 'United Kingdom', '1511578314322-dfad948ad7f4'],
            [$b, 'event', 'Startup Pitch Night', 'Berlin', 'Germany', '1540575467063-178a50c2df87'],
            [$c, 'venue', 'Harbourview Conference Hall', 'Singapore', 'Singapore', '1519167758201-4175745b7622'],
            [$a, 'venue', 'Riverside Wedding Pavilion', 'Cape Town', 'South Africa', '1469371670287-ac92ce23df18'],
        ];

        foreach ($rows as [$owner, $type, $title, $city, $country, $photo]) {
            if (! $owner) {
                continue;
            }
            $slug = Str::slug($title).'-ev'.$owner->customer_id;
            EventsVenuesAdvert::updateOrCreate(
                ['slug' => $slug],
                $this->onlyExistingColumns('events_venues_adverts', [
                    'user_id' => $owner->customer_id,
                    'category_id' => $categoryId,
                    'advert_type' => $type,
                    'title' => $title,
                    'description' => $title.' listed by '.$this->sellerName($owner).'.',
                    'short_description' => $title,
                    'event_date' => $type === 'event' ? now()->addDays(45)->toDateString() : null,
                    'venue_name' => $type === 'venue' ? $title : 'Main Hall',
                    'country' => $country,
                    'city' => $city,
                    'contact_name' => $this->sellerName($owner),
                    'business_name' => $this->sellerName($owner).' Events',
                    'email' => $owner->email,
                    'phone' => $owner->mobile_number ?? $owner->phone ?? '+44-7700-900000',
                    'main_image' => $this->img($photo),
                    'images' => [$this->img($photo)],
                    'status' => 'active',
                    'is_active' => true,
                    'is_featured' => true,
                    'free_event' => $type === 'event',
                ])
            );
        }
    }
}

