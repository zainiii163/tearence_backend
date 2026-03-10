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
        Schema::create('venue_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('service_category', ['catering', 'dj', 'decor', 'photography', 'security', 'event_planner', 'av_equipment', 'transportation', 'other']);
            $table->string('country');
            $table->string('city');
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->text('description');
            $table->json('packages')->nullable(); // Different service packages offered
            $table->json('availability')->nullable(); // Available dates/times
            $table->string('website')->nullable();
            $table->string('contact_email');
            $table->json('social_links')->nullable();
            $table->json('portfolio_images')->nullable();
            $table->string('video_link')->nullable();
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'spotlight'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            $table->index(['service_category', 'is_active']);
            $table->index(['country', 'city', 'is_active']);
            $table->index(['promotion_tier', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venue_services');
    }
};
