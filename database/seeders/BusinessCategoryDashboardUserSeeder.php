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
 * Safe to re-run (upserts by email / business slug).
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
            // Prefer real-looking emails (browser email fields often reject *.local)
            $email = $demo['slug'].'-demo@worldwideadverts.info';
            $legacyEmail = $demo['slug'].'@demo.wwa.local';
            $nameParts = explode(' ', $demo['owner'], 2);

            $customerAttrs = [
                'first_name' => $nameParts[0] ?? 'Business',
                'last_name' => $nameParts[1] ?? 'Owner',
                'password_hash' => $password,
                'phone' => '+4477009'.str_pad((string) abs(crc32($demo['slug']) % 100000), 5, '0', STR_PAD_LEFT),
                'email_verified_at' => now(),
                'customer_uid' => Str::random(10),
            ];

            if (Schema::hasColumn('customer', 'user_type')) {
                $customerAttrs['user_type'] = 'business';
            }
            if (Schema::hasColumn('customer', 'business_category')) {
                $customerAttrs['business_category'] = $demo['slug'];
            }
            if (Schema::hasColumn('customer', 'city')) {
                $customerAttrs['city'] = $demo['city'];
            }
            if (Schema::hasColumn('customer', 'country')) {
                $customerAttrs['country'] = 'United Kingdom';
            }

            $customer = Customer::updateOrCreate(
                ['email' => $email],
                $customerAttrs
            );

            // Keep legacy .local accounts in sync too (password refresh)
            Customer::updateOrCreate(
                ['email' => $legacyEmail],
                array_merge($customerAttrs, [
                    'first_name' => $nameParts[0] ?? 'Business',
                    'last_name' => $nameParts[1] ?? 'Owner',
                ])
            );

            $touch = [];
            if (Schema::hasColumn('customer', 'user_type')) {
                $touch['user_type'] = 'business';
            }
            if (Schema::hasColumn('customer', 'business_category')) {
                $touch['business_category'] = $demo['slug'];
            }
            if ($touch) {
                $customer->fill($touch)->save();
            }

            $bizSlug = 'demo-'.$demo['slug'].'-dashboard';
            $bizPayload = [
                'customer_id' => $customer->customer_id,
                'business_name' => $demo['name'],
                'business_description' => 'Demo '.$demo['slug'].' category dashboard business for Worldwide Adverts QA.',
                'business_phone_number' => $customer->phone,
                'business_address' => '1 Demo Street, '.$demo['city'].', UK',
                'city' => $demo['city'],
                'country' => 'United Kingdom',
                'business_email' => $email,
                'business_website' => 'https://worldwideadverts.info',
                'business_owner' => $demo['owner'],
                'status' => 'active',
                'personal_email' => $email,
                'personal_phone_number' => $customer->phone,
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
}
