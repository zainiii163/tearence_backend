<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add detailed flat columns to properties so PropertyStoreRequest fields
     * can persist directly (instead of going into the legacy `specifications` JSON).
     */
    public function up(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $columns = [
                // Location additions
                'region'                  => fn($t) => $t->string('region', 100)->nullable(),
                'show_exact_location'     => fn($t) => $t->boolean('show_exact_location')->default(true),

                // Residential
                'bedrooms'                => fn($t) => $t->unsignedSmallInteger('bedrooms')->nullable(),
                'bathrooms'               => fn($t) => $t->unsignedSmallInteger('bathrooms')->nullable(),
                'property_size'           => fn($t) => $t->decimal('property_size', 12, 2)->nullable(),
                'size_unit'               => fn($t) => $t->enum('size_unit', ['sq_m', 'sq_ft'])->default('sq_ft'),
                'furnished'               => fn($t) => $t->boolean('furnished')->default(false),
                'parking_spaces'          => fn($t) => $t->unsignedSmallInteger('parking_spaces')->nullable(),

                // Commercial
                'commercial_type'         => fn($t) => $t->string('commercial_type', 50)->nullable(),
                'floor_area'              => fn($t) => $t->decimal('floor_area', 12, 2)->nullable(),
                'footfall_rating'         => fn($t) => $t->string('footfall_rating', 20)->nullable(),
                'accessibility_features'  => fn($t) => $t->boolean('accessibility_features')->default(false),

                // Industrial
                'zoning_type'             => fn($t) => $t->string('zoning_type', 100)->nullable(),
                'warehouse_size'          => fn($t) => $t->decimal('warehouse_size', 12, 2)->nullable(),
                'loading_bays'            => fn($t) => $t->unsignedSmallInteger('loading_bays')->nullable(),
                'power_capacity'          => fn($t) => $t->decimal('power_capacity', 12, 2)->nullable(),
                'ceiling_height'          => fn($t) => $t->decimal('ceiling_height', 6, 2)->nullable(),

                // Land
                'land_size'               => fn($t) => $t->decimal('land_size', 14, 2)->nullable(),
                'land_type'               => fn($t) => $t->string('land_type', 50)->nullable(),
                'planning_permission'     => fn($t) => $t->string('planning_permission', 50)->nullable(),
                'soil_quality'            => fn($t) => $t->string('soil_quality', 500)->nullable(),

                // Luxury
                'premium_features'        => fn($t) => $t->json('premium_features')->nullable(),
                'security_features'       => fn($t) => $t->json('security_features')->nullable(),
                'view_type'               => fn($t) => $t->string('view_type', 50)->nullable(),

                // Investment
                'rental_yield'            => fn($t) => $t->decimal('rental_yield', 6, 2)->nullable(),
                'occupancy_rate'          => fn($t) => $t->decimal('occupancy_rate', 6, 2)->nullable(),
                'current_rental_income'   => fn($t) => $t->decimal('current_rental_income', 15, 2)->nullable(),
                'roi_percentage'          => fn($t) => $t->decimal('roi_percentage', 6, 2)->nullable(),

                // Pricing extras
                'deposit_required'        => fn($t) => $t->decimal('deposit_required', 15, 2)->nullable(),

                // Description split fields
                'overview'                => fn($t) => $t->text('overview')->nullable(),
                'key_features'            => fn($t) => $t->text('key_features')->nullable(),
                'nearby_amenities'        => fn($t) => $t->text('nearby_amenities')->nullable(),
                'additional_notes'        => fn($t) => $t->text('additional_notes')->nullable(),
            ];

            foreach ($columns as $name => $builder) {
                if (!Schema::hasColumn('properties', $name)) {
                    $builder($table);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('properties')) {
            return;
        }

        Schema::table('properties', function (Blueprint $table) {
            $drop = [
                'region', 'show_exact_location',
                'bedrooms', 'bathrooms', 'property_size', 'size_unit', 'furnished', 'parking_spaces',
                'commercial_type', 'floor_area', 'footfall_rating', 'accessibility_features',
                'zoning_type', 'warehouse_size', 'loading_bays', 'power_capacity', 'ceiling_height',
                'land_size', 'land_type', 'planning_permission', 'soil_quality',
                'premium_features', 'security_features', 'view_type',
                'rental_yield', 'occupancy_rate', 'current_rental_income', 'roi_percentage',
                'deposit_required',
                'overview', 'key_features', 'nearby_amenities', 'additional_notes',
            ];

            foreach ($drop as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
