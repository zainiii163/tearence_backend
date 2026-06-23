<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles_adverts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            
            // Vehicle Type & Category
            $table->enum('vehicle_type', ['car', 'van', 'motorbike', 'truck', 'bus', 'coach', 'electric_vehicle', 'classic_car', 'luxury_vehicle', 'caravan', 'motorhome', 'boat', 'jet_ski', 'agricultural', 'construction', 'other']);
            $table->enum('category', ['sale', 'hire', 'lease']);
            
            // Basic Vehicle Information
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->string('registration_number')->nullable();
            $table->string('vin')->nullable();
            $table->enum('condition', ['new', 'used', 'certified_pre_owned', 'refurbished']);
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid', 'lpg', 'other']);
            $table->enum('transmission', ['automatic', 'manual', 'cvt', 'dual_clutch', 'other']);
            $table->enum('body_type', ['sedan', 'hatchback', 'suv', 'coupe', 'convertible', 'wagon', 'pickup', 'van', 'truck', 'bus', 'motorbike', 'other']);
            $table->integer('mileage')->nullable();
            $table->integer('engine_size')->nullable();
            $table->integer('horsepower')->nullable();
            $table->integer('number_of_doors')->nullable();
            $table->integer('number_of_seats')->nullable();
            $table->string('colour')->nullable();
            
            // Pricing
            $table->decimal('price', 12, 2);
            $table->string('currency', 3)->default('GBP');
            $table->boolean('price_negotiable')->default(false);
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->string('payment_frequency')->nullable(); // daily, weekly, monthly
            
            // Hire/Lease Specific
            $table->boolean('insurance_included')->default(false);
            $table->boolean('maintenance_included')->default(false);
            $table->integer('minimum_hire_period')->nullable();
            $table->integer('maximum_hire_period')->nullable();
            $table->text('hire_terms')->nullable();
            $table->text('lease_terms')->nullable();
            
            // Description
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('overview')->nullable();
            $table->text('key_features')->nullable();
            $table->text('specifications')->nullable();
            $table->text('modifications')->nullable();
            $table->text('service_history')->nullable();
            $table->text('mot_details')->nullable();
            
            // Location
            $table->string('country');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_approximate_location')->default(false);
            $table->string('delivery_radius')->nullable();
            $table->boolean('delivery_available')->default(false);
            
            // Media
            $table->string('main_image');
            $table->json('images')->nullable();
            $table->string('video_link')->nullable();
            
            // Contact Information
            $table->string('contact_name');
            $table->string('business_name')->nullable();
            $table->string('phone_number');
            $table->string('email');
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('verified_seller')->default(false);
            $table->boolean('dealership')->default(false);
            $table->text('seller_description')->nullable();
            
            // Additional Information
            $table->json('additional_features')->nullable();
            $table->text('why_buy_this_vehicle')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Promotion
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'top_of_category', 'network_boost'])->default('standard');
            $table->decimal('promotion_price', 10, 2)->nullable();
            $table->timestamp('promotion_start')->nullable();
            $table->timestamp('promotion_end')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'expired', 'sold'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Analytics
            $table->integer('view_count')->default(0);
            $table->integer('contact_count')->default(0);
            $table->integer('save_count')->default(0);
            
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['vehicle_type', 'category']);
            $table->index(['country', 'city']);
            $table->index('price');
            $table->index('promotion_tier');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles_adverts');
    }
};
