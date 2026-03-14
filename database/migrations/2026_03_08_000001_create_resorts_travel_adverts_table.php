<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resorts_travel_adverts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable(); // FK constraint removed due to migration order
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->enum('advert_type', ['accommodation', 'transport', 'experience']);
            $table->enum('accommodation_type', ['resort', 'hotel', 'bnb', 'guest_house', 'holiday_home', 'villa', 'lodge'])->nullable();
            $table->enum('transport_type', ['airport_transfer', 'taxi_chauffeur', 'car_hire', 'shuttle_bus', 'tour_bus', 'boat_ferry', 'motorbike_scooter'])->nullable();
            $table->enum('experience_type', ['tours', 'excursions', 'adventure_packages', 'wellness_retreats'])->nullable();
            $table->string('country');
            $table->string('city');
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('price_per_night', 10, 2)->nullable();
            $table->decimal('price_per_trip', 10, 2)->nullable();
            $table->decimal('price_per_service', 10, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->date('availability_start')->nullable();
            $table->date('availability_end')->nullable();
            $table->json('room_types')->nullable();
            $table->json('amenities')->nullable();
            $table->integer('distance_to_city_centre')->nullable();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->integer('guest_capacity')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('passenger_capacity')->nullable();
            $table->integer('luggage_capacity')->nullable();
            $table->text('service_area')->nullable();
            $table->json('operating_hours')->nullable();
            $table->boolean('airport_pickup')->default(false);
            $table->string('duration')->nullable();
            $table->integer('group_size')->nullable();
            $table->text('whats_included')->nullable();
            $table->text('what_to_bring')->nullable();
            $table->longText('description');
            $table->text('overview')->nullable();
            $table->text('key_features')->nullable();
            $table->text('why_travellers_love_this')->nullable();
            $table->text('nearby_attractions')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('contact_name');
            $table->string('business_name')->nullable();
            $table->string('phone_number');
            $table->string('email');
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('verified_business')->default(false);
            $table->json('images')->nullable();
            $table->string('video_link')->nullable();
            $table->string('main_image')->nullable();
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'network_wide'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_approximate_location')->default(false);
            $table->timestamps();

            $table->index(['advert_type', 'is_active']);
            $table->index(['country', 'city']);
            $table->index(['promotion_tier', 'is_active']);
            $table->index(['price_per_night']);
            $table->index(['price_per_trip']);
            $table->index(['price_per_service']);
            $table->fullText(['title', 'description', 'tagline']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('resorts_travel_adverts');
    }
};
