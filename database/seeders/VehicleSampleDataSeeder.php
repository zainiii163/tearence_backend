<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\Vehicle;
use App\Models\User;

class VehicleSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create a user for vehicles
        $user = User::first() ?: User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password')
        ]);

        // Add more vehicle categories if needed
        $categories = [
            ['name' => 'Cars', 'slug' => 'cars', 'description' => 'Passenger vehicles', 'sort_order' => 1],
            ['name' => 'Trucks', 'slug' => 'trucks', 'description' => 'Commercial trucks', 'sort_order' => 2],
            ['name' => 'Motorcycles', 'slug' => 'motorcycles', 'description' => 'Two-wheel vehicles', 'sort_order' => 3],
            ['name' => 'Vans', 'slug' => 'vans', 'description' => 'Commercial vans', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            VehicleCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }

        // Add sample vehicles
        $category = VehicleCategory::where('slug', 'cars')->first();
        $make = VehicleMake::where('name', 'Toyota')->first();
        $model = VehicleModel::where('name', 'Corolla')->first();

        if ($category && $make && $model) {
            Vehicle::firstOrCreate([
                'title' => 'Sample Toyota Corolla',
                'user_id' => $user->id,
                'category_id' => $category->id,
                'make_id' => $make->id,
                'model_id' => $model->id,
                'advert_type' => 'sale',
                'condition' => 'good',
                'year' => 2020,
                'mileage' => 45000,
                'fuel_type' => 'petrol',
                'transmission' => 'automatic',
                'price' => 15000.00,
                'country' => 'USA',
                'city' => 'New York',
                'description' => 'Well maintained family car in excellent condition',
                'status' => 'approved',
                'is_active' => true,
                'is_featured' => true,
            ]);
        }
    }
}
