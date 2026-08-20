<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleCategory;
use App\Models\Vehicle;

/**
 * Canonical vehicle categories for the Worldwide Adverts Vehicles hub.
 * Sale, hire and lease are listing types within each category, not separate categories.
 */
class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cars',
                'slug' => 'cars',
                'description' => 'Cars for sale, hire and lease',
                'icon' => 'car',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Motorbikes',
                'slug' => 'motorbikes',
                'description' => 'Motorbikes for sale, hire and lease',
                'icon' => 'motorbike',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Commercial Vehicles',
                'slug' => 'commercial-vehicles',
                'description' => 'Commercial vehicles for sale, hire and lease',
                'icon' => 'truck',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Construction Vehicles',
                'slug' => 'construction-vehicles',
                'description' => 'Construction vehicles for sale, hire and lease',
                'icon' => 'excavator',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Plant Vehicles',
                'slug' => 'plant-vehicles',
                'description' => 'Plant machinery and vehicles for sale, hire and lease',
                'icon' => 'plant',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Vehicle Parts',
                'slug' => 'vehicle-parts',
                'description' => 'Parts for cars, trucks, bikes and all vehicles',
                'icon' => 'parts',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Caravans',
                'slug' => 'caravans',
                'description' => 'Caravans and motorhomes',
                'icon' => 'caravan',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Coaches',
                'slug' => 'coaches',
                'description' => 'Coaches for sale, hire and lease',
                'icon' => 'bus',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Farm Equipment & Vehicles',
                'slug' => 'farm-equipment',
                'description' => 'Farm equipment and vehicles for sale, hire and lease',
                'icon' => 'tractor',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Transport & Logistics',
                'slug' => 'transport-logistics',
                'description' => 'Transport, haulage and logistics services',
                'icon' => 'logistics',
                'is_active' => true,
                'sort_order' => 10,
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

        VehicleCategory::whereNotIn('slug', array_unique($keepSlugs))
            ->update(['is_active' => false]);

        Vehicle::where('title', 'Sample Toyota Corolla')->update([
            'is_featured' => false,
            'is_promoted' => false,
            'is_sponsored' => false,
        ]);
    }
}
