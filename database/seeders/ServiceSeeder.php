<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        // Create Service Categories
        $categories = [
            ['name' => 'Web Development', 'slug' => 'web-development', 'description' => 'Custom websites, web applications, and e-commerce solutions', 'icon' => 'code', 'sort_order' => 1],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'description' => 'iOS and Android app development', 'icon' => 'mobile', 'sort_order' => 2],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'description' => 'SEO, social media, and online advertising', 'icon' => 'chart-line', 'sort_order' => 3],
            ['name' => 'Content Creation', 'slug' => 'content-creation', 'description' => 'Writing, design, and multimedia content', 'icon' => 'pen', 'sort_order' => 4],
            ['name' => 'Consulting', 'slug' => 'consulting', 'description' => 'Business and technical consulting services', 'icon' => 'briefcase', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            DB::table('service_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Service Providers
        $providers = [
            ['user_id' => 2, 'business_name' => 'Tech Solutions Pro', 'bio' => 'Full-stack development agency with 10+ years experience', 'phone' => '+1234567890', 'website' => 'https://techsolutions.com', 'country' => 'United States', 'city' => 'New York', 'rating' => 4.8, 'review_count' => 127],
            ['user_id' => 3, 'business_name' => 'Mobile Masters', 'bio' => 'Specialized in iOS and Android development', 'phone' => '+1234567891', 'website' => 'https://mobilemasters.com', 'country' => 'United States', 'city' => 'San Francisco', 'rating' => 4.9, 'review_count' => 89],
            ['user_id' => 4, 'business_name' => 'Marketing Gurus', 'bio' => 'Digital marketing experts', 'phone' => '+1234567892', 'website' => 'https://marketinggurus.com', 'country' => 'United Kingdom', 'city' => 'London', 'rating' => 4.7, 'review_count' => 156],
        ];

        foreach ($providers as $provider) {
            DB::table('service_providers')->insert(array_merge($provider, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Services
        $services = [
            [
                'user_id' => 2,
                'service_provider_id' => 1,
                'category_id' => 1,
                'title' => 'Custom Website Development',
                'slug' => 'custom-website-development',
                'tagline' => 'Professional websites tailored to your needs',
                'description' => 'We create custom websites that are responsive, fast, and optimized for search engines. Our team works with you to understand your business needs and deliver a website that helps you achieve your goals.',
                'service_type' => 'freelance',
                'starting_price' => 1500.00,
                'country' => 'United States',
                'city' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'service_area_radius' => 100,
                'status' => 'active',
                'promotion_type' => 'standard',
                'languages' => json_encode(['English', 'Spanish']),
            ],
            [
                'user_id' => 3,
                'service_provider_id' => 2,
                'category_id' => 2,
                'title' => 'iOS App Development',
                'slug' => 'ios-app-development',
                'tagline' => 'Native iOS apps for iPhone and iPad',
                'description' => 'We develop high-quality iOS applications using Swift and SwiftUI. Our apps are optimized for performance, user experience, and App Store guidelines.',
                'service_type' => 'freelance',
                'starting_price' => 5000.00,
                'country' => 'United States',
                'city' => 'San Francisco',
                'latitude' => 37.7749,
                'longitude' => -122.4194,
                'service_area_radius' => 50,
                'status' => 'active',
                'promotion_type' => 'featured',
                'languages' => json_encode(['English']),
            ],
            [
                'user_id' => 4,
                'service_provider_id' => 3,
                'category_id' => 3,
                'title' => 'SEO Optimization',
                'slug' => 'seo-optimization',
                'tagline' => 'Improve your search engine rankings',
                'description' => 'Our SEO experts will analyze your website and implement strategies to improve your search engine rankings and drive organic traffic.',
                'service_type' => 'business',
                'starting_price' => 800.00,
                'country' => 'United Kingdom',
                'city' => 'London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'service_area_radius' => 200,
                'status' => 'active',
                'promotion_type' => 'promoted',
                'languages' => json_encode(['English', 'French', 'German']),
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->insert(array_merge($service, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Service Packages
        $packages = [
            [
                'service_id' => 1,
                'name' => 'Basic Website',
                'description' => '5-page responsive website with basic features',
                'price' => 1500.00,
                'delivery_time' => 14,
                'features' => json_encode(['Responsive Design', 'Contact Form', 'Basic SEO', '1 Year Support']),
                'revisions' => 2,
                'sort_order' => 1,
            ],
            [
                'service_id' => 1,
                'name' => 'Professional Website',
                'description' => '10-page website with advanced features and CMS',
                'price' => 3000.00,
                'delivery_time' => 21,
                'features' => json_encode(['Responsive Design', 'CMS Integration', 'Advanced SEO', 'E-commerce Ready', '2 Years Support']),
                'revisions' => 5,
                'sort_order' => 2,
            ],
            [
                'service_id' => 2,
                'name' => 'Basic iOS App',
                'description' => 'Simple iOS app with core features',
                'price' => 5000.00,
                'delivery_time' => 30,
                'features' => json_encode(['Native iOS Development', 'Basic UI/UX', 'App Store Submission', '3 Months Support']),
                'revisions' => 3,
                'sort_order' => 1,
            ],
        ];

        foreach ($packages as $package) {
            DB::table('service_packages')->insert(array_merge($package, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Service Media
        $media = [
            [
                'service_id' => 1,
                'type' => 'image',
                'file_path' => 'services/web-dev-1.jpg',
                'file_name' => 'web-development-portfolio-1.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 245760,
                'caption' => 'Custom website project',
                'is_thumbnail' => true,
                'sort_order' => 1,
            ],
            [
                'service_id' => 2,
                'type' => 'image',
                'file_path' => 'services/ios-app-1.jpg',
                'file_name' => 'ios-app-portfolio-1.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 327680,
                'caption' => 'iOS app screenshot',
                'is_thumbnail' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($media as $item) {
            DB::table('service_media')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Service seeder completed successfully!');
    }
}
