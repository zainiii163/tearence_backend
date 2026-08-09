<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;
use App\Models\Vehicle;

/**
 * Vehicle categories aligned with CarServicesLtd.com service lanes
 * (FAQ / marketplace categories), adapted for Worldwide Adverts Vehicles hub.
 */
class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cars for Sale',
                'slug' => 'cars-for-sale',
                'description' => 'Passenger cars listed for purchase',
                'icon' => 'car',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Cars for Hire',
                'slug' => 'cars-for-hire',
                'description' => 'Cars available for daily, weekly or monthly hire',
                'icon' => 'car-hire',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Car Share',
                'slug' => 'car-share',
                'description' => 'Car sharing and pooling listings',
                'icon' => 'car-share',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Chauffeur / Drivers for Hire',
                'slug' => 'chauffeur-drivers',
                'description' => 'Professional drivers and chauffeur services',
                'icon' => 'chauffeur',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Tow Services',
                'slug' => 'tow-services',
                'description' => 'Breakdown recovery and tow truck services',
                'icon' => 'tow',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Car / Truck Mechanics',
                'slug' => 'mechanics',
                'description' => 'Repair shops and mobile mechanics',
                'icon' => 'mechanic',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Car & Truck Parts',
                'slug' => 'parts',
                'description' => 'Spare parts, accessories and consumables',
                'icon' => 'parts',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Farm Equipment Hire & Sale',
                'slug' => 'farm-equipment',
                'description' => 'Agricultural machinery for hire or sale',
                'icon' => 'tractor',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Commercial Vehicles Hire & Sale',
                'slug' => 'commercial-vehicles',
                'description' => 'Vans, HGVs and fleet vehicles for hire or sale',
                'icon' => 'truck',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Motorbikes',
                'slug' => 'motorbikes',
                'description' => 'Motorcycles, scooters and two-wheelers',
                'icon' => 'motorbike',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Construction Vehicles',
                'slug' => 'construction-vehicles',
                'description' => 'Plant and construction machinery',
                'icon' => 'excavator',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Other Services',
                'slug' => 'other-services',
                'description' => 'Detailing, insurance, specialists and other vehicle services',
                'icon' => 'other',
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

        // Keep common legacy slugs active so old listings still route
        $legacyAliases = [
            'cars' => ['name' => 'Cars for Sale', 'sort_order' => 1],
            'motorcycles' => ['name' => 'Motorbikes', 'sort_order' => 10],
            'trucks' => ['name' => 'Commercial Vehicles Hire & Sale', 'sort_order' => 9],
            'agricultural-vehicles' => ['name' => 'Farm Equipment Hire & Sale', 'sort_order' => 8],
        ];
        foreach ($legacyAliases as $slug => $meta) {
            if (VehicleCategory::where('slug', $slug)->exists()) {
                VehicleCategory::where('slug', $slug)->update([
                    'is_active' => true,
                    'name' => $meta['name'],
                    'sort_order' => $meta['sort_order'],
                ]);
                $keepSlugs[] = $slug;
            }
        }

        VehicleCategory::whereNotIn('slug', array_unique($keepSlugs))
            ->update(['is_active' => false]);

        Vehicle::where('title', 'Sample Toyota Corolla')->update([
            'is_featured' => false,
            'is_promoted' => false,
            'is_sponsored' => false,
        ]);
    }
}
