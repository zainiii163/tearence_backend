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
        Schema::create('promoted_adverts', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable(); // max 80 characters
            $table->text('description');
            $table->json('key_features')->nullable(); // structured features
            $table->text('special_notes')->nullable();
            
            // Category and Type
            $table->string('advert_type'); // Product, Service, Property, Vehicle, Job, Event, Business, Miscellaneous
            $table->foreignId('category_id')->nullable()->constrained('promoted_advert_categories')->onDelete('set null');
            
            // Location
            $table->string('country');
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('location_privacy', ['exact', 'approximate'])->default('exact');
            
            // Pricing
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->enum('price_type', ['fixed', 'negotiable', 'free'])->default('fixed');
            $table->enum('condition', ['new', 'used', 'not_applicable'])->nullable();
            
            // Media
            $table->string('main_image');
            $table->json('additional_images')->nullable(); // up to 10 images
            $table->string('video_link')->nullable();
            
            // Seller Information
            $table->string('seller_name');
            $table->string('business_name')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('verified_seller')->default(false);
            
            // Promotion Tiers (4-tier system)
            $table->enum('promotion_tier', [
                'promoted_basic',     // £X
                'promoted_plus',      // £XX (Most Popular)
                'promoted_premium',   // £XXX
                'network_wide_boost'  // £XXXX
            ])->default('promoted_basic');
            
            $table->decimal('promotion_price', 10, 2)->default(0);
            $table->date('promotion_start')->nullable();
            $table->date('promotion_end')->nullable();
            
            // Visibility and Analytics
            $table->integer('views_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->integer('inquiries_count')->default(0);
            
            // Status
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'expired'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('approved_at')->nullable();
            
            // User relationship
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'is_active'], 'pa_status_active_idx');
            $table->index(['promotion_tier', 'promotion_start', 'promotion_end'], 'pa_promotion_dates_idx');
            $table->index(['advert_type'], 'pa_type_idx');
            $table->index(['category_id'], 'pa_category_idx');
            $table->index(['country'], 'pa_country_idx');
            $table->index(['views_count'], 'pa_views_idx');
            $table->index(['saves_count'], 'pa_saves_idx');
            $table->index(['created_at'], 'pa_created_idx');
            $table->index(['is_featured'], 'pa_featured_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoted_adverts');
    }
};
