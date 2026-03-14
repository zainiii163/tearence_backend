<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;
use Illuminate\Support\Facades\DB;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cars',
                'slug' => 'cars',
                'description' => 'Passenger vehicles including sedans, hatchbacks, SUVs, and more',
                'icon' => 'car',
                'image' => 'categories/cars.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vans',
                'slug' => 'vans',
                'description' => 'Commercial and passenger vans for business and personal use',
                'icon' => 'van',
                'image' => 'categories/vans.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Motorbikes',
                'slug' => 'motorbikes',
                'description' => 'Motorcycles, scooters, and other two-wheeled vehicles',
                'icon' => 'motorbike',
                'image' => 'categories/motorbikes.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Trucks & Lorries',
                'slug' => 'trucks-lorries',
                'description' => 'Heavy duty trucks and lorries for commercial use',
                'icon' => 'truck',
                'image' => 'categories/trucks.jpg',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Buses & Coaches',
                'slug' => 'buses-coaches',
                'description' => 'Passenger buses and coaches for public and private transport',
                'icon' => 'bus',
                'image' => 'categories/buses.jpg',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Electric Vehicles',
                'slug' => 'electric-vehicles',
                'description' => 'Electric and hybrid vehicles for eco-friendly transportation',
                'icon' => 'electric-car',
                'image' => 'categories/electric.jpg',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Classic Cars',
                'slug' => 'classic-cars',
                'description' => 'Vintage and classic collector vehicles',
                'icon' => 'classic-car',
                'image' => 'categories/classic.jpg',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Luxury & Exotic',
                'slug' => 'luxury-exotic',
                'description' => 'Premium luxury and exotic sports cars',
                'icon' => 'luxury-car',
                'image' => 'categories/luxury.jpg',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Caravans & Motorhomes',
                'slug' => 'caravans-motorhomes',
                'description' => 'Recreational vehicles for camping and travel',
                'icon' => 'caravan',
                'image' => 'categories/caravans.jpg',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Boats & Jet Skis',
                'slug' => 'boats-jet-skis',
                'description' => 'Water vehicles including boats, yachts, and jet skis',
                'icon' => 'boat',
                'image' => 'categories/boats.jpg',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Agricultural Vehicles',
                'slug' => 'agricultural-vehicles',
                'description' => 'Farm and agricultural machinery',
                'icon' => 'tractor',
                'image' => 'categories/agricultural.jpg',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Construction Vehicles',
                'slug' => 'construction-vehicles',
                'description' => 'Heavy construction and industrial vehicles',
                'icon' => 'excavator',
                'image' => 'categories/construction.jpg',
                'is_active' => true,
                'sort_order' => 12,
            ],
        ];

        foreach ($categories as $category) {
            VehicleCategory::create($category);
        }
    }
}
