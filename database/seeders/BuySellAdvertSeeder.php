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
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1493663284031-b7e3aefcae8e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1567016432779-094069958ea5?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1576678927484-cc899799fb56?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1614164185128-e4ec99c436d7?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?auto=format&fit=crop&w=800&q=80'
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
                    'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1615066390971-03e4e1c36ddf?auto=format&fit=crop&w=800&q=80'
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
