<?php

namespace Database\Seeders;

use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds paid live marketplace listings from Customer sellers (API owners),
 * with working Unsplash image URLs (no broken /img placeholders).
 */
class MarketplaceLiveListingsSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::query()->where('email', 'admin@worldwideadverts.com')->first()
            ?? User::query()->where('is_super_admin', true)->first()
            ?? User::query()->orderBy('user_id')->first();

        if (! $adminUser) {
            $adminUser = User::create([
                'user_uid' => (string) Str::uuid(),
                'first_name' => 'WWA',
                'last_name' => 'Admin',
                'email' => 'admin@worldwideadverts.com',
                'password' => Hash::make('password'),
                'is_super_admin' => true,
                'email_verified_at' => now(),
                'email_verified' => true,
            ]);
        }

        // Marketplace listings are owned by Customer (API auth), not admin User.
        $admin = Customer::query()->where('email', 'john.doe@example.com')->first();
        if (! $admin) {
            $admin = Customer::create([
                'customer_uid' => Str::random(10),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'password_hash' => Hash::make('password123'),
                'affiliated_members' => 0,
            ]);
        }

        $seller = Customer::query()->where('email', 'seller.demo@worldwideadverts.com')->first();
        if (! $seller) {
            $seller = Customer::create([
                'customer_uid' => Str::random(10),
                'first_name' => 'Demo',
                'last_name' => 'Seller',
                'email' => 'seller.demo@worldwideadverts.com',
                'password_hash' => Hash::make('password123'),
                'affiliated_members' => 0,
            ]);
        }

        $categoryId = BuySellCategory::query()->value('id');
        if (! $categoryId) {
            $this->command?->error('No buy-sell categories found. Run BuySellCategorySeeder first.');

            return;
        }

        $listings = [
            [
                'owner' => $admin,
                'title' => 'Premium Leather Messenger Bag',
                'description' => 'Genuine leather messenger with laptop sleeve. Excellent condition, paid featured placement.',
                'price' => 189.00,
                'country' => 'USA',
                'city' => 'Miami',
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1590874103328-eac38a683ce7?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => true,
                'is_promoted' => true,
            ],
            [
                'owner' => $admin,
                'title' => 'Studio Lighting Kit',
                'description' => 'Complete softbox kit for product photography. Catalog-quality listing.',
                'price' => 249.00,
                'country' => 'UK',
                'city' => 'London',
                'images' => [
                    'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => true,
                'is_promoted' => false,
            ],
            [
                'owner' => $seller,
                'title' => 'Seller: Vintage Polaroid Camera',
                'description' => 'Working Polaroid from a verified demo seller. Contact email and phone are real for this account.',
                'price' => 95.00,
                'country' => 'USA',
                'city' => 'Austin',
                'images' => [
                    'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => false,
                'is_promoted' => true,
            ],
            [
                'owner' => $seller,
                'title' => 'Seller: Wireless Noise-Cancel Headphones',
                'description' => 'User-posted paid listing. Ask the seller via Contact Seller — messages are stored and emailed.',
                'price' => 129.00,
                'country' => 'Canada',
                'city' => 'Toronto',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => false,
                'is_promoted' => false,
            ],
            [
                'owner' => $seller,
                'title' => 'Seller: Mountain Bike — Carbon Frame',
                'description' => 'Paid listing with real seller contact details. Excellent condition.',
                'price' => 780.00,
                'country' => 'USA',
                'city' => 'Denver',
                'images' => [
                    'https://images.unsplash.com/photo-1485965120187-cf692dd03298?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => true,
                'is_promoted' => true,
            ],
        ];

        foreach ($listings as $row) {
            /** @var Customer $owner */
            $owner = $row['owner'];
            $sellerName = trim(($owner->first_name ?? '').' '.($owner->last_name ?? '')) ?: ($owner->email ?? 'Seller');

            BuySellAdvert::updateOrCreate(
                [
                    'title' => $row['title'],
                    'user_id' => $owner->customer_id,
                ],
                [
                    'description' => $row['description'],
                    'category_id' => $categoryId,
                    'condition' => 'good',
                    'price' => $row['price'],
                    'currency' => 'USD',
                    'negotiable' => true,
                    'country' => $row['country'],
                    'city' => $row['city'],
                    'seller_name' => $sellerName,
                    'seller_email' => $owner->email,
                    'seller_phone' => $owner->mobile_number ?? $owner->phone ?? '+1-305-555-0100',
                    'show_phone' => true,
                    'preferred_contact' => 'email',
                    'images' => $row['images'],
                    'status' => 'active',
                    'featured' => $row['featured'],
                    'is_promoted' => $row['is_promoted'],
                    'verified_seller' => true,
                    'promotion_plan' => 'paid',
                    'promotion_status' => 'active',
                    'expires_at' => now()->addDays(60),
                ]
            );
        }

        $this->command?->info('MarketplaceLiveListingsSeeder: seeded customer-owned paid listings with live images.');
    }
}
