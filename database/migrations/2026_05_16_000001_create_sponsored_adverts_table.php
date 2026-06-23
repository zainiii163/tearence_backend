<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponsored_adverts', function (Blueprint $table) {
            $table->id();
            
            // Basic Advert Information
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('tagline', 80)->nullable();
            $table->text('description');
            $table->string('advert_type'); // product, service, property, job, event, vehicle, business, other
            $table->string('category', 100)->nullable();
            $table->string('condition', 50)->nullable(); // new, used, not_applicable
            
            // Pricing
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            
            // Location
            $table->string('country', 100);
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_approximate_location')->default(false);
            
            // Media
            $table->string('main_image', 500)->nullable();
            $table->json('additional_images')->nullable();
            $table->string('video_link', 500)->nullable();
            
            // Seller/Poster Information
            $table->string('contact_name', 255);
            $table->string('business_name', 255)->nullable();
            $table->string('phone', 50);
            $table->string('email', 255);
            $table->string('website', 500)->nullable();
            $table->json('social_links')->nullable();
            $table->string('logo', 500)->nullable();
            $table->boolean('verified_seller')->default(false);
            
            // Sponsored Tier Information
            $table->enum('sponsored_tier', ['basic', 'plus', 'premium'])->default('basic');
            $table->decimal('promotion_price', 10, 2)->default(0);
            $table->timestamp('promotion_start')->nullable();
            $table->timestamp('promotion_end')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('saves_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            
            // Status and Moderation
            $table->enum('status', ['active', 'inactive', 'pending', 'rejected'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            
            // Terms and Conditions
            $table->boolean('agreed_to_terms')->default(false);
            $table->boolean('accurate_info')->default(false);
            
            // Foreign Keys
            $table->unsignedInteger('user_id')->nullable();
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['advert_type']);
            $table->index(['category']);
            $table->index(['country']);
            $table->index(['city']);
            $table->index(['sponsored_tier']);
            $table->index(['status']);
            $table->index(['is_active']);
            $table->index(['payment_status']);
            $table->index(['views_count']);
            $table->index(['saves_count']);
            $table->index(['rating']);
            $table->index(['created_at']);
            $table->index(['promotion_start']);
            $table->index(['promotion_end']);
            $table->index(['verified_seller']);
            
            // Full-text search index
            $table->fullText(['title', 'tagline', 'description', 'business_name'], 'sponsored_search_index');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponsored_adverts');
    }
};
