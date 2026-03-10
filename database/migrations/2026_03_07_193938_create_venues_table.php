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
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('venue_type', ['wedding_hall', 'conference_centre', 'party_hall', 'outdoor_space', 'hotel_banquet', 'bar_restaurant', 'meeting_room', 'exhibition_space', 'sports_venue', 'other']);
            $table->string('country');
            $table->string('city');
            $table->integer('capacity');
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->text('description');
            $table->json('amenities')->nullable(); // Wi-Fi, Parking, Catering, AV Equipment, etc.
            $table->boolean('indoor')->default(true);
            $table->boolean('outdoor')->default(false);
            $table->boolean('catering_available')->default(false);
            $table->boolean('parking_available')->default(false);
            $table->boolean('accessibility')->default(false);
            $table->json('opening_hours')->nullable();
            $table->string('booking_link')->nullable();
            $table->string('contact_email');
            $table->json('social_links')->nullable();
            $table->json('images')->nullable();
            $table->string('floor_plan')->nullable();
            $table->string('video_link')->nullable();
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'spotlight'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            $table->index(['venue_type', 'is_active']);
            $table->index(['country', 'city', 'is_active']);
            $table->index(['capacity', 'is_active']);
            $table->index(['promotion_tier', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
