<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['name' => 'Cars', 'slug' => 'cars', 'description' => 'Cars for sale, hire and lease', 'icon' => 'car', 'sort_order' => 1],
            ['name' => 'Motorbikes', 'slug' => 'motorbikes', 'description' => 'Motorbikes for sale, hire and lease', 'icon' => 'motorbike', 'sort_order' => 2],
            ['name' => 'Commercial Vehicles', 'slug' => 'commercial-vehicles', 'description' => 'Commercial vehicles for sale, hire and lease', 'icon' => 'truck', 'sort_order' => 3],
            ['name' => 'Construction Vehicles', 'slug' => 'construction-vehicles', 'description' => 'Construction vehicles for sale, hire and lease', 'icon' => 'excavator', 'sort_order' => 4],
            ['name' => 'Plant Vehicles', 'slug' => 'plant-vehicles', 'description' => 'Plant machinery and vehicles for sale, hire and lease', 'icon' => 'plant', 'sort_order' => 5],
            ['name' => 'Vehicle Parts', 'slug' => 'vehicle-parts', 'description' => 'Parts for cars, trucks, bikes and all vehicles', 'icon' => 'parts', 'sort_order' => 6],
            ['name' => 'Caravans', 'slug' => 'caravans', 'description' => 'Caravans and motorhomes', 'icon' => 'caravan', 'sort_order' => 7],
            ['name' => 'Coaches', 'slug' => 'coaches', 'description' => 'Coaches for sale, hire and lease', 'icon' => 'bus', 'sort_order' => 8],
            ['name' => 'Farm Equipment & Vehicles', 'slug' => 'farm-equipment', 'description' => 'Farm equipment and vehicles for sale, hire and lease', 'icon' => 'tractor', 'sort_order' => 9],
            ['name' => 'Transport & Logistics', 'slug' => 'transport-logistics', 'description' => 'Transport, haulage and logistics services', 'icon' => 'logistics', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            DB::table('vehicle_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, ['is_active' => true, 'updated_at' => now()])
            );
        }

        $legacyMap = [
            'cars-for-sale' => 'cars',
            'cars-for-hire' => 'cars',
            'car-share' => 'cars',
            'chauffeur-drivers' => 'transport-logistics',
            'tow-services' => 'transport-logistics',
            'mechanics' => 'vehicle-parts',
            'parts' => 'vehicle-parts',
            'motorcycles' => 'motorbikes',
            'trucks' => 'commercial-vehicles',
            'vans' => 'commercial-vehicles',
            'agricultural-vehicles' => 'farm-equipment',
            'other-services' => 'transport-logistics',
        ];

        foreach ($legacyMap as $legacySlug => $canonicalSlug) {
            $legacyId = DB::table('vehicle_categories')->where('slug', $legacySlug)->value('id');
            $canonicalId = DB::table('vehicle_categories')->where('slug', $canonicalSlug)->value('id');
            if (!$legacyId || !$canonicalId || $legacyId === $canonicalId) {
                continue;
            }

            DB::table('vehicles')->where('category_id', $legacyId)->update(['category_id' => $canonicalId]);
        }

        DB::table('vehicle_categories')
            ->whereNotIn('slug', array_column($categories, 'slug'))
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Category consolidation is intentionally irreversible; listings remain valid.
    }
};
