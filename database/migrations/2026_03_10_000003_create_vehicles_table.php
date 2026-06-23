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
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('make_id');
            $table->unsignedBigInteger('model_id');
            
            // Basic Information
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->enum('advert_type', ['sale', 'hire', 'lease', 'transport_service'])->default('sale');
            $table->enum('condition', ['new', 'used', 'excellent', 'good', 'fair'])->default('used');
            
            // Vehicle Specifications
            $table->year('year');
            $table->unsignedBigInteger('mileage')->nullable();
            $table->string('fuel_type')->nullable(); // petrol, diesel, electric, hybrid
            $table->string('transmission')->nullable(); // manual, automatic, cvt
            $table->string('engine_size')->nullable();
            $table->string('color')->nullable();
            $table->integer('doors')->nullable();
            $table->integer('seats')->nullable();
            $table->string('body_type')->nullable(); // sedan, hatchback, suv, etc.
            $table->string('vin')->nullable(); // Vehicle Identification Number
            $table->string('registration_number')->nullable();
            
            // Commercial Vehicle Specific
            $table->decimal('payload_capacity', 10, 2)->nullable(); // for trucks/vans
            $table->integer('axles')->nullable();
            $table->string('emission_class')->nullable();
            
            // Boat Specific
            $table->decimal('length', 8, 2)->nullable(); // for boats
            $table->string('engine_type')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('trailer_included')->default(false);
            
            // Transport Service Specific
            $table->text('service_area')->nullable();
            $table->string('operating_hours')->nullable();
            $table->integer('passenger_capacity')->nullable();
            $table->integer('luggage_capacity')->nullable();
            $table->boolean('airport_pickup')->default(false);
            
            // Pricing
            $table->decimal('price', 12, 2)->nullable();
            $table->enum('price_type', ['fixed', 'per_day', 'per_week', 'per_month', 'per_hour'])->default('fixed');
            $table->boolean('negotiable')->default(false);
            $table->decimal('deposit', 10, 2)->nullable();
            
            // Media
            $table->string('main_image')->nullable();
            $table->json('additional_images')->nullable();
            $table->string('video_link')->nullable();
            
            // Location
            $table->string('country');
            $table->string('city');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('show_exact_location')->default(true);
            
            // Status and Visibility
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_top_of_category')->default(false);
            
            // Analytics
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('saves')->default(0);
            $table->unsignedBigInteger('enquiries')->default(0);
            
            // Additional Information
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website')->nullable();
            $table->json('features')->nullable(); // JSON array of vehicle features
            $table->text('service_history')->nullable();
            $table->string('mot_expiry')->nullable();
            $table->string('road_tax_status')->nullable();
            $table->integer('previous_owners')->nullable();
            
            // Upgrades and Expiry
            $table->unsignedBigInteger('pricing_plan_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_active']);
            $table->index(['advert_type', 'category_id']);
            $table->index(['country', 'city']);
            $table->index(['price', 'advert_type']);
            $table->index(['is_featured', 'is_sponsored', 'is_promoted']);
        });

        // Skip foreign key constraints for now due to issues
        // TODO: Add foreign key constraints in a separate migration after verifying table structures
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
