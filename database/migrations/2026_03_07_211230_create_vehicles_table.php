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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('vehicle_category_id'); // FK constraint removed due to migration order
            $table->enum('advert_type', ['sale', 'hire', 'lease', 'transport_service']);
            $table->string('title', 200);
            $table->string('tagline', 255)->nullable();
            $table->text('description');
            $table->string('make', 100);
            $table->string('model', 100);
            $table->integer('year');
            $table->integer('mileage')->nullable();
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid', 'lpg', 'other']);
            $table->enum('transmission', ['manual', 'automatic', 'semi-automatic', 'cvt']);
            $table->enum('condition', ['new', 'used', 'excellent', 'good', 'fair']);
            $table->enum('body_type', ['saloon', 'hatchback', 'suv', 'mpv', 'coupe', 'convertible', 'pickup', 'van', 'truck', 'bus', 'motorbike', 'boat', 'other'])->nullable();
            $table->decimal('price', 12, 2);
            $table->enum('price_type', ['fixed', 'per_day', 'per_week', 'per_month', 'negotiable']);
            $table->string('colour', 50)->nullable();
            $table->integer('doors')->nullable();
            $table->integer('seats')->nullable();
            $table->string('engine_size', 20)->nullable();
            $table->string('registration_number', 20)->nullable();
            $table->date('mot_expiry')->nullable();
            $table->enum('service_history', ['full', 'partial', 'none'])->nullable();
            $table->integer('previous_owners')->nullable();
            $table->decimal('payload', 8, 2)->nullable();
            $table->integer('axles')->nullable();
            $table->string('emission_class', 20)->nullable();
            $table->string('vehicle_type', 50)->nullable();
            $table->string('engine_type', 50)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('trailer_included')->default(false);
            $table->string('service_area', 255)->nullable();
            $table->string('operating_hours', 100)->nullable();
            $table->integer('passenger_capacity')->nullable();
            $table->integer('luggage_capacity')->nullable();
            $table->boolean('airport_pickup')->default(false);
            $table->string('video_link', 500)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name', 255)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sold')->default(false);
            $table->integer('views_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('enquiries_count')->default(0);
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('promoted_until')->nullable();
            $table->timestamp('sponsored_until')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['vehicle_category_id', 'is_active']);
            $table->index(['advert_type', 'is_active']);
            $table->index(['make', 'model']);
            $table->index(['price']);
            $table->index(['year']);
            $table->index(['mileage']);
            $table->index(['is_verified', 'is_active']);
            $table->index(['featured_until']);
            $table->index(['promoted_until']);
            $table->index(['sponsored_until']);
            $table->index(['country', 'city']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
