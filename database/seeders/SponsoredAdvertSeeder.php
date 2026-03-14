<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SponsoredAdvert;
use App\Models\SponsoredCategory;

class SponsoredAdvertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = SponsoredCategory::pluck('id')->toArray();
        
        $sampleAdverts = [
            [
                'user_id' => 1,
                'title' => 'Luxury Villa with Ocean View',
                'description' => 'Stunning 5-bedroom villa with panoramic ocean views, private pool, and modern amenities. Perfect for families or those seeking luxury living.',
                'price' => 2500000,
                'currency' => 'USD',
                'category_id' => $categories[0] ?? 1,
                'country' => 'United Arab Emirates',
                'city' => 'Dubai',
                'images' => ['https://example.com/villa1.jpg', 'https://example.com/villa2.jpg'],
                'video_url' => 'https://youtube.com/watch?v=example',
                'seller_info' => [
                    'business_name' => 'Premium Properties',
                    'contact_email' => 'contact@premium.com',
                    'phone' => '+971501234567',
                    'website' => 'https://premiumproperties.com',
                ],
                'location' => [
                    'address' => 'Palm Jumeirah',
                    'coordinates' => [25.2048, 55.2708],
                ],
                'status' => 'active',
                'featured' => true,
                'promoted' => false,
                'sponsored' => true,
                'promotion_plan' => 'sponsored',
                'views' => 15420,
                'rating' => 4.9,
            ],
            [
                'user_id' => 2,
                'title' => 'Modern Office Space Downtown',
                'description' => 'Fully furnished office space in the heart of downtown. High-speed internet, meeting rooms, and 24/7 access.',
                'price' => 5000,
                'currency' => 'USD',
                'category_id' => $categories[1] ?? 2,
                'country' => 'United States',
                'city' => 'New York',
                'images' => ['https://example.com/office1.jpg'],
                'seller_info' => [
                    'business_name' => 'NYC Office Rentals',
                    'contact_email' => 'info@nycoffice.com',
                    'phone' => '+12125551234',
                ],
                'status' => 'active',
                'featured' => false,
                'promoted' => true,
                'sponsored' => false,
                'promotion_plan' => 'promoted',
                'views' => 8500,
                'rating' => 4.5,
            ],
            [
                'user_id' => 3,
                'title' => 'Professional Photography Services',
                'description' => 'Award-winning photographer available for weddings, corporate events, and portraits. Professional equipment and editing services.',
                'price' => 1500,
                'currency' => 'USD',
                'category_id' => $categories[4] ?? 3,
                'country' => 'United Kingdom',
                'city' => 'London',
                'images' => ['https://example.com/photo1.jpg', 'https://example.com/photo2.jpg'],
                'seller_info' => [
                    'business_name' => 'Creative Lens Photography',
                    'contact_email' => 'hello@creativelens.com',
                    'phone' => '+447207123456',
                ],
                'status' => 'active',
                'featured' => false,
                'promoted' => false,
                'sponsored' => false,
                'promotion_plan' => 'free',
                'views' => 3200,
                'rating' => 4.8,
            ],
        ];

        foreach ($sampleAdverts as $advert) {
            SponsoredAdvert::create($advert);
        }
    }
}
