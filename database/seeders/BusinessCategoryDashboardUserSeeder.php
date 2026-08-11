<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerBusiness;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Demo business users — one per homepage category dashboard.
 * Login: {category}-demo@worldwideadverts.info  /  Dashboard@123
 * Safe to re-run (does not overwrite customer_uid on existing rows).
 */
class BusinessCategoryDashboardUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Dashboard@123');

        $demos = [
            ['slug' => 'buy-sell', 'name' => 'Buy & Sell Hub Co', 'owner' => 'Buy Sell Owner', 'city' => 'London'],
            ['slug' => 'business', 'name' => 'WWA Business Directory Co', 'owner' => 'Biz Owner', 'city' => 'Manchester'],
            ['slug' => 'services', 'name' => 'Pro Services Collective', 'owner' => 'Service Owner', 'city' => 'Birmingham'],
            ['slug' => 'property', 'name' => 'Homes & Estates Partners', 'owner' => 'Property Owner', 'city' => 'Leeds'],
            ['slug' => 'jobs', 'name' => 'Career Posts Ltd', 'owner' => 'Jobs Owner', 'city' => 'Bristol'],
            ['slug' => 'software', 'name' => 'Script & SaaS Sellers', 'owner' => 'Software Owner', 'city' => 'Cambridge'],
            ['slug' => 'events', 'name' => 'Events & Venues Live', 'owner' => 'Events Owner', 'city' => 'Edinburgh'],
            ['slug' => 'adverts', 'name' => 'Adverts Campaign Desk', 'owner' => 'Ads Owner', 'city' => 'Glasgow'],
            ['slug' => 'funding', 'name' => 'Funding & Crowdfund Co', 'owner' => 'Funding Owner', 'city' => 'London'],
            ['slug' => 'stores', 'name' => 'Marketplace Store Ops', 'owner' => 'Store Owner', 'city' => 'Liverpool'],
            ['slug' => 'books', 'name' => 'Authors & Books Desk', 'owner' => 'Books Owner', 'city' => 'Oxford'],
            ['slug' => 'vehicles', 'name' => 'Fleet & Motors Trading', 'owner' => 'Vehicles Owner', 'city' => 'Coventry'],
            ['slug' => 'donations', 'name' => 'Causes & Donations Trust', 'owner' => 'Donations Owner', 'city' => 'Cardiff'],
            ['slug' => 'images', 'name' => 'Stock Media Studio', 'owner' => 'Images Owner', 'city' => 'Brighton'],
            ['slug' => 'classifieds', 'name' => 'Local Classifieds Desk', 'owner' => 'Classifieds Owner', 'city' => 'Sheffield'],
            ['slug' => 'affiliate', 'name' => 'Affiliate Offers HQ', 'owner' => 'Affiliate Owner', 'city' => 'London'],
            ['slug' => 'resorts', 'name' => 'Resorts & Travel Desk', 'owner' => 'Travel Owner', 'city' => 'Bournemouth'],
            ['slug' => 'investment', 'name' => 'Investment Opportunities Co', 'owner' => 'Invest Owner', 'city' => 'London'],
        ];

        $resolveCategoryId = function (string $slug) {
            if (!Schema::hasTable('category') && !Schema::hasTable('categories')) {
                return null;
            }

            return Category::where('slug', $slug)->value('category_id')
                ?? Category::where('slug', 'like', '%'.$slug.'%')->value('category_id');
        };

        foreach ($demos as $demo) {
            $email = $demo['slug'].'-demo@worldwideadverts.info';
            $nameParts = explode(' ', $demo['owner'], 2);
            $phone = '+4477009'.str_pad((string) abs(crc32($demo['slug']) % 100000), 5, '0', STR_PAD_LEFT);

            $customer = Customer::where('email', $email)->first();

            $attrs = [
                'first_name' => $nameParts[0] ?? 'Business',
                'last_name' => $nameParts[1] ?? 'Owner',
                'password_hash' => $password,
                'phone' => $phone,
                'email_verified_at' => now(),
            ];
            if (Schema::hasColumn('customer', 'user_type')) {
                $attrs['user_type'] = 'business';
            }
            if (Schema::hasColumn('customer', 'business_category')) {
                $attrs['business_category'] = $demo['slug'];
            }
            if (Schema::hasColumn('customer', 'city')) {
                $attrs['city'] = $demo['city'];
            }
            if (Schema::hasColumn('customer', 'country')) {
                $attrs['country'] = 'United Kingdom';
            }

            if ($customer) {
                // Never rewrite customer_uid on existing rows (unique constraint)
                $customer->fill($attrs)->save();
            } else {
                $attrs['email'] = $email;
                $attrs['customer_uid'] = $this->uniqueCustomerUid('d'.$demo['slug']);
                $customer = Customer::create($attrs);
            }

            $bizSlug = 'demo-'.$demo['slug'].'-dashboard';
            $bizPayload = [
                'customer_id' => $customer->customer_id,
                'business_name' => $demo['name'],
                'business_description' => 'Demo '.$demo['slug'].' category dashboard business for Worldwide Adverts QA.',
                'business_phone_number' => $phone,
                'business_address' => '1 Demo Street, '.$demo['city'].', UK',
                'city' => $demo['city'],
                'country' => 'United Kingdom',
                'business_email' => $email,
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => $demo['owner'],
                'status' => 'active',
                'personal_email' => $email,
                'personal_phone_number' => $phone,
                'category_id' => $resolveCategoryId($demo['slug']),
                'category_profile' => [
                    'dashboard_category' => $demo['slug'],
                    'seeded' => true,
                    'views_30d' => random_int(40, 900),
                    'leads' => random_int(0, 40),
                    'enquiries' => random_int(0, 35),
                    'listings_count' => random_int(1, 12),
                    'highlights' => ['Seeded demo for '.$demo['slug'].' dashboard'],
                ],
            ];

            if (Schema::hasColumn('customer_business', 'business_category_slug')) {
                $bizPayload['business_category_slug'] = $demo['slug'];
            }

            CustomerBusiness::updateOrCreate(
                ['slug' => $bizSlug],
                $bizPayload
            );

            $this->command?->info("Seeded: {$email} / Dashboard@123 → {$demo['slug']}");
        }

        $this->command?->newLine();
        $this->command?->info('Password for all demo users: Dashboard@123');
        $this->command?->info('Example: vehicles-demo@worldwideadverts.info');
    }

    protected function uniqueCustomerUid(string $seed): string
    {
        // Deterministic-ish 10-char uid, then fall back to random if taken
        $base = substr(preg_replace('/[^A-Za-z0-9]/', '', 'd'.md5($seed)), 0, 10);
        if ($base === '' || Customer::where('customer_uid', $base)->exists()) {
            do {
                $base = Str::random(10);
            } while (Customer::where('customer_uid', $base)->exists());
        }

        return $base;
    }
}
