<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;
use App\Models\Vehicle;

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
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Vans',
                'slug' => 'vans',
                'description' => 'Commercial and passenger vans for business and personal use',
                'icon' => 'van',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Motorcycles',
                'slug' => 'motorcycles',
                'description' => 'Motorcycles, scooters, and other two-wheeled vehicles',
                'icon' => 'motorbike',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Trucks',
                'slug' => 'trucks',
                'description' => 'Heavy duty trucks and lorries for commercial use',
                'icon' => 'truck',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Buses & Coaches',
                'slug' => 'buses-coaches',
                'description' => 'Passenger buses and coaches for public and private transport',
                'icon' => 'bus',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Electric Vehicles',
                'slug' => 'electric-vehicles',
                'description' => 'Electric and hybrid vehicles for eco-friendly transportation',
                'icon' => 'electric-car',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Classic Cars',
                'slug' => 'classic-cars',
                'description' => 'Vintage and classic collector vehicles',
                'icon' => 'classic-car',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Luxury & Exotic',
                'slug' => 'luxury-exotic',
                'description' => 'Premium luxury and exotic sports cars',
                'icon' => 'luxury-car',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Caravans & Motorhomes',
                'slug' => 'caravans-motorhomes',
                'description' => 'Recreational vehicles for camping and travel',
                'icon' => 'caravan',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Boats & Jet Skis',
                'slug' => 'boats-jet-skis',
                'description' => 'Water vehicles including boats, yachts, and jet skis',
                'icon' => 'boat',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Agricultural Vehicles',
                'slug' => 'agricultural-vehicles',
                'description' => 'Farm and agricultural machinery',
                'icon' => 'tractor',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Construction Vehicles',
                'slug' => 'construction-vehicles',
                'description' => 'Heavy construction and industrial vehicles',
                'icon' => 'excavator',
                'is_active' => true,
                'sort_order' => 12,
            ],
        ];

        $keepSlugs = [];
        foreach ($categories as $category) {
            $keepSlugs[] = $category['slug'];
            VehicleCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        // Also keep legacy "motorbikes" slug as alias of motorcycles if present
        VehicleCategory::where('slug', 'motorbikes')->update([
            'is_active' => true,
            'name' => 'Motorcycles',
            'sort_order' => 3,
        ]);

        // Deactivate junk / test categories that aren't in the canonical list
        VehicleCategory::whereNotIn('slug', array_merge($keepSlugs, ['motorbikes']))
            ->update(['is_active' => false]);

        // Demo sample should not appear as Featured
        Vehicle::where('title', 'Sample Toyota Corolla')->update([
            'is_featured' => false,
            'is_promoted' => false,
            'is_sponsored' => false,
        ]);
    }
}
