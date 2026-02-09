<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Location;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SampleListingsSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create sample customer
        $customer = Customer::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'customer_uid' => Str::random(13),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+1234567890',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Get or create sample category
        $category = Category::firstOrCreate(
            ['name' => 'Electronics'],
            [
                'slug' => 'electronics',
                'description' => 'Electronic devices and accessories',
            ]
        );

        // Get or create sample location
        $location = Location::firstOrCreate(
            ['city' => 'New York'],
            [
                'zip' => '10001',
                'latitude' => '40.7128',
                'longitude' => '-74.0060',
            ]
        );

        // Get or create sample currency
        $currency = Currency::firstOrCreate(
            ['code' => 'USD'],
            [
                'name' => 'US Dollar',
                'symbol' => '$',
            ]
        );

        // Create sample listings with different approval statuses
        $this->createApprovedListings($customer, $category, $location, $currency);
        $this->createPendingListings($customer, $category, $location, $currency);
        $this->createRejectedListings($customer, $category, $location, $currency);
        $this->createHarmfulListings($customer, $category, $location, $currency);
        $this->createOldListings($customer, $category, $location, $currency);

        $this->command->info('Sample listings created for testing ad moderation system');
    }

    private function createApprovedListings($customer, $category, $location, $currency): void
    {
        // Regular approved listing
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'iPhone 13 Pro Max - Excellent Condition',
            'slug' => 'iphone-13-pro-max-excellent-condition',
            'description' => 'Like new iPhone 13 Pro Max, 256GB, excellent condition, comes with original box and accessories. No scratches or issues.',
            'price' => 899.99,
            'type' => 'sale',
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => 1, // Admin user ID
            'approved_at' => now()->subDays(2),
            'post_type' => 'regular',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subDays(10),
        ]);

        // Sponsored approved listing
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'MacBook Pro 16" - Premium Deal',
            'slug' => 'macbook-pro-16-premium-deal',
            'description' => 'Latest MacBook Pro 16" with M2 Pro chip, 32GB RAM, 1TB SSD. Perfect for professionals. Includes AppleCare+.',
            'price' => 2499.99,
            'type' => 'sale',
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now()->subDays(1),
            'post_type' => 'sponsored',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subDays(5),
        ]);

        // Admin post
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'Official Platform Announcement - New Features',
            'slug' => 'official-platform-announcement-new-features',
            'description' => 'Check out our amazing new features! We\'ve added KYC verification, advanced moderation tools, and much more. This is an official admin post.',
            'price' => 0.00,
            'type' => 'announcement',
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_by' => 1,
            'approved_at' => now()->subHours(6),
            'post_type' => 'admin',
            'is_admin_post' => true,
            'is_harmful' => false,
            'created_at' => now()->subHours(6),
        ]);
    }

    private function createPendingListings($customer, $category, $location, $currency): void
    {
        // Regular pending listing
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'Samsung Galaxy S23 - Brand New',
            'slug' => 'samsung-galaxy-s23-brand-new',
            'description' => 'Brand new Samsung Galaxy S23, 256GB, sealed in box. Latest model with warranty.',
            'price' => 799.99,
            'type' => 'sale',
            'status' => 'active',
            'approval_status' => 'pending',
            'post_type' => 'regular',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subHours(3),
        ]);

        // Promoted pending listing
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'iPad Air - Great Deal',
            'slug' => 'ipad-air-great-deal',
            'description' => 'iPad Air 5th generation, 64GB, excellent condition, includes Apple Pencil and case.',
            'price' => 599.99,
            'type' => 'sale',
            'status' => 'active',
            'approval_status' => 'pending',
            'post_type' => 'promoted',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subHours(1),
        ]);
    }

    private function createRejectedListings($customer, $category, $location, $currency): void
    {
        // Rejected listing - inappropriate content
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'Get Rich Quick - Guaranteed Returns',
            'slug' => 'get-rich-quick-guaranteed-returns',
            'description' => 'Investment opportunity with guaranteed 1000% returns in 30 days. No risk involved. Contact me for details.',
            'price' => 99.99,
            'type' => 'service',
            'status' => 'inactive',
            'approval_status' => 'rejected',
            'rejection_reason' => 'Violates community guidelines - appears to be a scam or fraudulent scheme',
            'post_type' => 'regular',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subDays(2),
        ]);

        // Rejected listing - prohibited items
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'Collection of Rare Items - Contact for Details',
            'slug' => 'collection-of-rare-items-contact-for-details',
            'description' => 'Various rare and collectible items available. Contact me for complete list and pricing. Some items may require special permits.',
            'price' => 5000.00,
            'type' => 'sale',
            'status' => 'inactive',
            'approval_status' => 'rejected',
            'rejection_reason' => 'Prohibited items - potentially illegal or regulated items',
            'post_type' => 'regular',
            'is_admin_post' => false,
            'is_harmful' => false,
            'created_at' => now()->subDay(),
        ]);
    }

    private function createHarmfulListings($customer, $category, $location, $currency): void
    {
        // Harmful listing - suspicious pricing
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'iPhone 14 Pro - Unbelievable Price',
            'slug' => 'iphone-14-pro-unbelievable-price',
            'description' => 'iPhone 14 Pro, 512GB, perfect condition. Selling for only $50 because I need money urgently today. Western Union only.',
            'price' => 50.00,
            'type' => 'sale',
            'status' => 'active',
            'approval_status' => 'approved',
            'post_type' => 'regular',
            'is_admin_post' => false,
            'is_harmful' => true,
            'moderation_notes' => 'Flagged by automated system: Unrealistic pricing for high-value item, urgent payment request',
            'created_at' => now()->subDays(15),
        ]);

        // Harmful listing - suspicious contact info
        Listing::create([
            'customer_id' => $customer->customer_id,
            'category_id' => $category->category_id,
            'location_id' => $location->location_id,
            'currency_id' => $currency->currency_id,
            'title' => 'Bank Transfer Available - Help Me',
            'slug' => 'bank-transfer-available-help-me',
            'description' => 'I can help you transfer money internationally. Send me your bank details and I\'ll explain how this works. Call me at +1-800-SCAM-123 for immediate assistance.',
            'price' => 100.00,
            'type' => 'service',
            'status' => 'active',
            'approval_status' => 'approved',
            'post_type' => 'regular',
            'is_admin_post' => false,
            'is_harmful' => true,
            'moderation_notes' => 'Flagged by automated system: Contains suspicious contact information and potential money laundering scheme',
            'created_at' => now()->subDays(20),
        ]);
    }

    private function createOldListings($customer, $category, $location, $currency): void
    {
        // Old listings (30+ days) for cleanup testing
        for ($i = 1; $i <= 3; $i++) {
            Listing::create([
                'customer_id' => $customer->customer_id,
                'category_id' => $category->category_id,
                'location_id' => $location->location_id,
                'currency_id' => $currency->currency_id,
                'title' => "Old Listing Item {$i} - Expired",
                'slug' => "old-listing-item-{$i}-expired",
                'description' => "This is an old listing that should be cleaned up by the automated system. Created {$i} months ago.",
                'price' => 100.00 * $i,
                'type' => 'sale',
                'status' => 'expired',
                'approval_status' => 'approved',
                'approved_by' => 1,
                'approved_at' => now()->subDays(35),
                'post_type' => 'regular',
                'is_admin_post' => false,
                'is_harmful' => false,
                'created_at' => now()->subDays(35),
            ]);
        }
    }
}
