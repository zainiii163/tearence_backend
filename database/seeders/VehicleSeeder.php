<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        // Create Vehicle Categories
        $categories = [
            ['name' => 'Cars', 'slug' => 'cars', 'description' => 'Passenger vehicles and cars', 'icon' => 'car', 'sort_order' => 1],
            ['name' => 'Motorcycles', 'slug' => 'motorcycles', 'description' => 'Motorcycles and scooters', 'icon' => 'motorcycle', 'sort_order' => 2],
            ['name' => 'Vans', 'slug' => 'vans', 'description' => 'Commercial vans and light trucks', 'icon' => 'truck', 'sort_order' => 3],
            ['name' => 'Trucks', 'slug' => 'trucks', 'description' => 'Heavy trucks and commercial vehicles', 'icon' => 'truck-pickup', 'sort_order' => 4],
            ['name' => 'Boats', 'slug' => 'boats', 'description' => 'Boats and watercraft', 'icon' => 'anchor', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            DB::table('vehicle_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Vehicles
        $vehicles = [
            [
                'user_id' => 2,
                'vehicle_category_id' => 1,
                'advert_type' => 'sale',
                'title' => 'Toyota Camry 2022',
                'tagline' => 'Excellent condition, low mileage',
                'description' => 'Well-maintained Toyota Camry with full service history. Perfect for daily commuting.',
                'make' => 'Toyota',
                'model' => 'Camry',
                'year' => 2022,
                'mileage' => 15000,
                'fuel_type' => 'petrol',
                'transmission' => 'automatic',
                'condition' => 'excellent',
                'body_type' => 'saloon',
                'price' => 25000.00,
                'price_type' => 'fixed',
                'colour' => 'Silver',
                'doors' => 4,
                'seats' => 5,
                'country' => 'United States',
                'city' => 'New York',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'is_verified' => true,
                'is_active' => true,
                'views_count' => 145,
                'saves_count' => 23,
                'enquiries_count' => 8,
            ],
            [
                'user_id' => 3,
                'vehicle_category_id' => 2,
                'advert_type' => 'sale',
                'title' => 'Harley Davidson Sportster 2021',
                'tagline' => 'Classic American motorcycle',
                'description' => 'Beautiful Harley Davidson Sportster in excellent condition. Low miles, always garaged.',
                'make' => 'Harley Davidson',
                'model' => 'Sportster',
                'year' => 2021,
                'mileage' => 5000,
                'fuel_type' => 'petrol',
                'transmission' => 'manual',
                'condition' => 'excellent',
                'body_type' => 'motorbike',
                'price' => 12000.00,
                'price_type' => 'fixed',
                'colour' => 'Black',
                'country' => 'United States',
                'city' => 'Los Angeles',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'is_verified' => true,
                'is_active' => true,
                'views_count' => 89,
                'saves_count' => 12,
                'enquiries_count' => 5,
            ],
            [
                'user_id' => 4,
                'vehicle_category_id' => 3,
                'advert_type' => 'hire',
                'title' => 'Ford Transit Van Rental',
                'tagline' => 'Perfect for moving and deliveries',
                'description' => 'Spacious Ford Transit van available for daily or weekly rental. Well-maintained and reliable.',
                'make' => 'Ford',
                'model' => 'Transit',
                'year' => 2020,
                'mileage' => 45000,
                'fuel_type' => 'diesel',
                'transmission' => 'manual',
                'condition' => 'good',
                'body_type' => 'van',
                'price' => 80.00,
                'price_type' => 'per_day',
                'colour' => 'White',
                'doors' => 3,
                'country' => 'United Kingdom',
                'city' => 'London',
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'is_verified' => true,
                'is_active' => true,
                'views_count' => 67,
                'saves_count' => 8,
                'enquiries_count' => 15,
            ],
            [
                'user_id' => 5,
                'vehicle_category_id' => 5,
                'advert_type' => 'sale',
                'title' => 'Bayliner Bowrider 2019',
                'tagline' => 'Great family boat for summer fun',
                'description' => 'Well-maintained Bayliner bowrider boat. Perfect for family outings and water sports.',
                'make' => 'Bayliner',
                'model' => 'Bowrider',
                'year' => 2019,
                'fuel_type' => 'petrol',
                'condition' => 'good',
                'body_type' => 'boat',
                'price' => 35000.00,
                'price_type' => 'fixed',
                'colour' => 'Blue',
                'seats' => 8,
                'length' => 18.5,
                'country' => 'United States',
                'city' => 'Miami',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'is_verified' => true,
                'is_active' => true,
                'views_count' => 112,
                'saves_count' => 18,
                'enquiries_count' => 6,
            ],
        ];

        foreach ($vehicles as $vehicle) {
            DB::table('vehicles')->insert(array_merge($vehicle, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Vehicle Images
        $images = [
            [
                'vehicle_id' => 1,
                'file_path' => 'vehicles/toyota-camry-1.jpg',
                'file_name' => 'toyota-camry-exterior.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 327680,
                'is_primary' => true,
                'sort_order' => 1,
            ],
            [
                'vehicle_id' => 1,
                'file_path' => 'vehicles/toyota-camry-2.jpg',
                'file_name' => 'toyota-camry-interior.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 294912,
                'is_primary' => false,
                'sort_order' => 2,
            ],
            [
                'vehicle_id' => 2,
                'file_path' => 'vehicles/harley-sportster-1.jpg',
                'file_name' => 'harley-sportster-side.jpg',
                'mime_type' => 'image/jpeg',
                'file_size' => 409600,
                'is_primary' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($images as $image) {
            DB::table('vehicle_images')->insert(array_merge($image, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Create Vehicle Upsells
        $upsells = [
            [
                'vehicle_id' => 1,
                'upsell_type' => 'featured',
                'price' => 25.00,
                'currency' => 'USD',
                'duration_days' => 7,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays(7),
                'status' => 'active',
            ],
            [
                'vehicle_id' => 2,
                'upsell_type' => 'promoted',
                'price' => 15.00,
                'currency' => 'USD',
                'duration_days' => 14,
                'starts_at' => Carbon::now(),
                'ends_at' => Carbon::now()->addDays(14),
                'status' => 'active',
            ],
        ];

        foreach ($upsells as $upsell) {
            DB::table('vehicle_upsells')->insert(array_merge($upsell, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Vehicle seeder completed successfully!');
    }
}
