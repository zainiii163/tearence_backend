<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BuySellAdvert;
use App\Models\BuySellCategory;
use App\Models\User;
use Illuminate\Support\Str;

class BuySellAdvertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some sample categories
        $electronicsCategory = BuySellCategory::where('slug', 'electronics')->first();
        $vehiclesCategory = BuySellCategory::where('slug', 'vehicles')->first();
        $homeCategory = BuySellCategory::where('slug', 'home-garden')->first();
        $fashionCategory = BuySellCategory::where('slug', 'fashion-accessories')->first();
        $sportsCategory = BuySellCategory::where('slug', 'sports-fitness')->first();

        // Sample adverts data
        $adverts = [
            [
                'title' => 'iPhone 14 Pro 256GB - Like New',
                'description' => 'Selling my iPhone 14 Pro 256GB in Deep Purple. The phone is in like-new condition, always kept in a case with screen protector. No scratches or dents. Battery health is at 95%. Comes with original box, charger, and unused earbuds.',
                'category_id' => $electronicsCategory?->id,
                'condition' => 'like_new',
                'price' => 899.99,
                'negotiable' => true,
                'country' => 'United States',
                'city' => 'New York',
                'state_province' => 'NY',
                'brand' => 'Apple',
                'model' => 'iPhone 14 Pro',
                'color' => 'Deep Purple',
                'seller_name' => 'John Smith',
                'seller_email' => 'john.smith@example.com',
                'seller_phone' => '+1234567890',
                'show_phone' => true,
                'preferred_contact' => 'email',
                'images' => [
                    'https://example.com/iphone14pro-1.jpg',
                    'https://example.com/iphone14pro-2.jpg',
                    'https://example.com/iphone14pro-3.jpg'
                ],
                'featured' => true,
                'is_promoted' => true,
                'views_count' => 245,
                'saves_count' => 18,
                'contacts_count' => 7,
            ],
            [
                'title' => '2021 Toyota Camry SE - Low Miles',
                'description' => 'Excellent condition 2021 Toyota Camry SE with only 25,000 miles. Regularly serviced, non-smoker owner. Features include: backup camera, lane departure warning, adaptive cruise control, premium audio system. Clean title, no accidents.',
                'category_id' => $vehiclesCategory?->id,
                'condition' => 'excellent',
                'price' => 24500.00,
                'negotiable' => false,
                'country' => 'United States',
                'city' => 'Los Angeles',
                'state_province' => 'CA',
                'brand' => 'Toyota',
                'model' => 'Camry SE',
                'color' => 'Midnight Black Metallic',
                'seller_name' => 'Sarah Johnson',
                'seller_email' => 'sarah.j@example.com',
                'seller_phone' => '+1234567891',
                'show_phone' => false,
                'preferred_contact' => 'email',
                'images' => [
                    'https://example.com/camry-1.jpg',
                    'https://example.com/camry-2.jpg',
                    'https://example.com/camry-3.jpg',
                    'https://example.com/camry-4.jpg'
                ],
                'video_url' => 'https://youtube.com/watch?v=example',
                'featured' => false,
                'is_promoted' => true,
                'views_count' => 189,
                'saves_count' => 23,
                'contacts_count' => 12,
            ],
            [
                'title' => 'Modern Leather Sofa Set - 3 Pieces',
                'description' => 'Beautiful modern leather sofa set including 3-seater sofa, loveseat, and armchair. Premium genuine leather in dark brown color. Less than 2 years old, selling due to moving. No pets, no smoking household.',
                'category_id' => $homeCategory?->id,
                'condition' => 'excellent',
                'price' => 1200.00,
                'negotiable' => true,
                'country' => 'United States',
                'city' => 'Chicago',
                'state_province' => 'IL',
                'brand' => 'Ashley Furniture',
                'material' => 'Genuine Leather',
                'dimensions' => 'Sofa: 84"W x 36"D x 32"H, Loveseat: 60"W x 36"D x 32"H, Chair: 36"W x 36"D x 32"H',
                'seller_name' => 'Michael Chen',
                'seller_email' => 'm.chen@example.com',
                'seller_phone' => '+1234567892',
                'show_phone' => true,
                'preferred_contact' => 'phone',
                'images' => [
                    'https://example.com/sofa-1.jpg',
                    'https://example.com/sofa-2.jpg',
                    'https://example.com/sofa-3.jpg'
                ],
                'views_count' => 156,
                'saves_count' => 31,
                'contacts_count' => 8,
            ],
            [
                'title' => 'Nike Air Jordan 1 Retro High - Size 10',
                'description' => 'Authentic Nike Air Jordan 1 Retro High in Chicago colorway. Worn only twice, practically new. Comes with original box and extra laces. 100% authentic, purchased from Nike store.',
                'category_id' => $fashionCategory?->id,
                'condition' => 'like_new',
                'price' => 350.00,
                'negotiable' => false,
                'country' => 'United States',
                'city' => 'Miami',
                'state_province' => 'FL',
                'brand' => 'Nike',
                'model' => 'Air Jordan 1 Retro High',
                'color' => 'Chicago Red/White/Black',
                'seller_name' => 'Alex Rodriguez',
                'seller_email' => 'alex.r@example.com',
                'seller_phone' => '+1234567893',
                'show_phone' => false,
                'preferred_contact' => 'email',
                'images' => [
                    'https://example.com/jordan1-1.jpg',
                    'https://example.com/jordan1-2.jpg'
                ],
                'is_sponsored' => true,
                'views_count' => 412,
                'saves_count' => 67,
                'contacts_count' => 23,
            ],
            [
                'title' => 'Peloton Bike+ - Excellent Condition',
                'description' => 'Peloton Bike+ in excellent condition. Used regularly for 1 year, selling because upgrading to Peloton Tread. Includes bike, mat, weights, and heart rate monitor. All original accessories included.',
                'category_id' => $sportsCategory?->id,
                'condition' => 'good',
                'price' => 1200.00,
                'negotiable' => true,
                'country' => 'United States',
                'city' => 'Seattle',
                'state_province' => 'WA',
                'brand' => 'Peloton',
                'model' => 'Bike+',
                'seller_name' => 'Emily Davis',
                'seller_email' => 'emily.d@example.com',
                'seller_phone' => '+1234567894',
                'show_phone' => true,
                'preferred_contact' => 'email',
                'images' => [
                    'https://example.com/peloton-1.jpg',
                    'https://example.com/peloton-2.jpg',
                    'https://example.com/peloton-3.jpg'
                ],
                'verified_seller' => true,
                'views_count' => 278,
                'saves_count' => 45,
                'contacts_count' => 15,
            ],
            [
                'title' => 'MacBook Pro 16" M1 Max - 32GB RAM',
                'description' => 'Top-spec MacBook Pro 16" with M1 Max chip, 32GB RAM, 1TB SSD. Space Gray color. Includes original charger, box, and AppleCare+ until December 2024. Perfect for video editing or development work.',
                'category_id' => $electronicsCategory?->id,
                'condition' => 'excellent',
                'price' => 2800.00,
                'negotiable' => false,
                'country' => 'United States',
                'city' => 'San Francisco',
                'state_province' => 'CA',
                'brand' => 'Apple',
                'model' => 'MacBook Pro 16"',
                'material' => 'Aluminum',
                'seller_name' => 'David Kim',
                'seller_email' => 'd.kim@example.com',
                'seller_phone' => '+1234567895',
                'show_phone' => false,
                'preferred_contact' => 'email',
                'images' => [
                    'https://example.com/macbook-1.jpg',
                    'https://example.com/macbook-2.jpg',
                    'https://example.com/macbook-3.jpg',
                    'https://example.com/macbook-4.jpg'
                ],
                'is_urgent' => true,
                'verified_seller' => true,
                'views_count' => 523,
                'saves_count' => 89,
                'contacts_count' => 34,
            ],
            [
                'title' => 'Vintage Rolex Submariner - 1978',
                'description' => 'Vintage Rolex Submariner from 1978, reference 1680. Beautiful patina on the dial, original tritium markers. Recently serviced by authorized Rolex dealer. Comes with box and papers. A true collector\'s piece.',
                'category_id' => $fashionCategory?->id,
                'condition' => 'good',
                'price' => 15000.00,
                'negotiable' => false,
                'country' => 'United States',
                'city' => 'Boston',
                'state_province' => 'MA',
                'brand' => 'Rolex',
                'model' => 'Submariner 1680',
                'seller_name' => 'Robert Wilson',
                'seller_email' => 'r.wilson@example.com',
                'seller_phone' => '+1234567896',
                'show_phone' => true,
                'preferred_contact' => 'phone',
                'images' => [
                    'https://example.com/rolex-1.jpg',
                    'https://example.com/rolex-2.jpg',
                    'https://example.com/rolex-3.jpg'
                ],
                'featured' => true,
                'is_sponsored' => true,
                'verified_seller' => true,
                'views_count' => 892,
                'saves_count' => 156,
                'contacts_count' => 67,
            ],
            [
                'title' => 'Professional DSLR Camera Kit - Canon 5D Mark IV',
                'description' => 'Canon 5D Mark IV with Canon 24-70mm f/2.8L II lens. Excellent condition, low shutter count (~15,000). Includes extra battery, vertical grip, and professional camera bag. Perfect for wedding or event photography.',
                'category_id' => $electronicsCategory?->id,
                'condition' => 'excellent',
                'price' => 3200.00,
                'negotiable' => true,
                'country' => 'United States',
                'city' => 'Austin',
                'state_province' => 'TX',
                'brand' => 'Canon',
                'model' => '5D Mark IV',
                'seller_name' => 'Lisa Martinez',
                'seller_email' => 'lisa.m@example.com',
                'seller_phone' => '+1234567897',
                'show_phone' => false,
                'preferred_contact' => 'email',
                'images' => [
                    'https://example.com/canon-1.jpg',
                    'https://example.com/canon-2.jpg',
                    'https://example.com/canon-3.jpg'
                ],
                'verified_seller' => true,
                'views_count' => 367,
                'saves_count' => 72,
                'contacts_count' => 28,
            ],
            [
                'title' => 'Dining Table Set - Solid Wood 6 Seater',
                'description' => 'Beautiful solid oak dining table with 6 matching chairs. Traditional design with turned legs. Table extends to seat 8 people. Some minor wear consistent with age but overall great condition.',
                'category_id' => $homeCategory?->id,
                'condition' => 'good',
                'price' => 800.00,
                'negotiable' => true,
                'country' => 'United States',
                'city' => 'Denver',
                'state_province' => 'CO',
                'material' => 'Solid Oak',
                'dimensions' => 'Table: 72"L x 36"W x 30"H, Extended: 96"L x 36"W x 30"H, Chairs: 18"W x 20"D x 36"H',
                'seller_name' => 'James Thompson',
                'seller_email' => 'j.thompson@example.com',
                'seller_phone' => '+1234567898',
                'show_phone' => true,
                'preferred_contact' => 'phone',
                'images' => [
                    'https://example.com/dining-1.jpg',
                    'https://example.com/dining-2.jpg'
                ],
                'views_count' => 134,
                'saves_count' => 28,
                'contacts_count' => 11,
            ],
        ];

        foreach ($adverts as $advertData) {
            // Generate UUID for each advert
            $advertData['id'] = (string) Str::uuid();
            
            // Set some default values
            $advertData['currency'] = 'USD';
            $advertData['status'] = 'active';
            $advertData['ip_address'] = '127.0.0.1';
            $advertData['user_agent'] = 'Seeder';
            
            // Create advert
            BuySellAdvert::create($advertData);
        }
    }
}
