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
        Schema::create('sponsored_adverts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('sponsored_advert_id');
            $table->string('title', 255);
            $table->string('tagline', 80)->nullable();
            $table->text('description');
            $table->text('overview')->nullable();
            $table->text('key_features')->nullable();
            $table->text('what_makes_special')->nullable();
            $table->text('why_sponsored')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Category and location
            $table->string('advert_type', 50); // Product, Service, Property, Job, Event, Vehicle, etc.
            $table->unsignedInteger('category_id')->nullable();
            $table->string('country', 100);
            $table->string('city', 100);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('location_precision', ['exact', 'approximate'])->default('approximate');
            
            // Pricing
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->enum('condition', ['new', 'used', 'not_applicable'])->nullable();
            
            // Media
            $table->string('main_image')->nullable();
            $table->json('additional_images')->nullable();
            $table->string('video_link')->nullable();
            
            // Seller information
            $table->string('seller_name', 255);
            $table->string('business_name', 255)->nullable();
            $table->string('phone', 50);
            $table->string('email', 255);
            $table->string('website', 255)->nullable();
            $table->json('social_links')->nullable();
            $table->string('logo', 255)->nullable();
            $table->boolean('verified_seller')->default(false);
            
            // Sponsorship tiers
            $table->enum('sponsorship_tier', ['basic', 'plus', 'premium'])->default('basic');
            $table->decimal('sponsorship_price', 10, 2);
            $table->string('payment_status', 20)->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->dateTime('sponsorship_start_date')->nullable();
            $table->dateTime('sponsorship_end_date')->nullable();
            
            // Visibility and engagement
            $table->integer('views_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('inquiries_count')->default(0);
            $table->float('rating')->default(0);
            $table->integer('rating_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            
            // Metadata
            $table->string('slug', 255)->unique();
            $table->json('tags')->nullable();
            $table->json('seo_meta')->nullable();
            
            // Foreign keys
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['advert_type', 'is_active']);
            $table->index(['country', 'city']);
            $table->index(['sponsorship_tier', 'is_active']);
            $table->index(['sponsorship_start_date', 'sponsorship_end_date'], 'sponsorship_dates_idx');
            $table->index(['views_count', 'rating']);
            $table->index(['created_at']);
            
            // Foreign key constraints
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_adverts');
    }
};
