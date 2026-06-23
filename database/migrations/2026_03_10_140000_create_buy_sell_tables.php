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
        // Create buy_sell_categories table if it doesn't exist
        if (!Schema::hasTable('buy_sell_categories')) {
            Schema::create('buy_sell_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->json('fields')->nullable(); // Dynamic fields for this category
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
            });
        }

        // Create buy_sell_items table if it doesn't exist
        if (!Schema::hasTable('buy_sell_items')) {
            Schema::create('buy_sell_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('item_type', ['for_sale', 'for_swap', 'give_away'])->default('for_sale');
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'poor'])->default('good');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->json('dimensions')->nullable(); // {length, width, height, unit}
            $table->decimal('weight', 8, 2)->nullable(); // in kg
            $table->longText('description');
            $table->json('key_features')->nullable();
            $table->json('usage_notes')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_negotiable')->default(true);
            $table->string('country');
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('location_details')->nullable();
            $table->integer('views')->default(0);
            $table->integer('contacts')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('shares')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->enum('status', ['draft', 'active', 'paused', 'sold', 'suspended'])->default('draft');
            $table->enum('promotion_type', ['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])->default('standard');
            $table->timestamp('promotion_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('expires_at')->nullable(); // Auto-expire for giveaways
            $table->json('meta_data')->nullable(); // Additional category-specific fields
            $table->timestamps();

            try {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            } catch (\Exception $e) {}
            $table->foreign('category_id')->references('id')->on('buy_sell_categories')->onDelete('restrict');
            
            $table->index(['status', 'promotion_type']);
            $table->index(['category_id', 'country']);
            $table->index(['item_type', 'status']);
            $table->index(['price', 'currency']);
            $table->fullText(['title', 'description']);
            });
        }

        // Create buy_sell_images table if it doesn't exist
        if (!Schema::hasTable('buy_sell_images')) {
            Schema::create('buy_sell_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            $table->index(['item_id', 'sort_order']);
            });
        }

        // Create buy_sell_videos table if it doesn't exist
        if (!Schema::hasTable('buy_sell_videos')) {
            Schema::create('buy_sell_videos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('video_path');
            $table->string('thumbnail_path')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            });
        }

        // Create buy_sell_sellers table if it doesn't exist
        if (!Schema::hasTable('buy_sell_sellers')) {
            Schema::create('buy_sell_sellers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('verification_data')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            });
        }

        // Create buy_sell_enquiries table if it doesn't exist
        if (!Schema::hasTable('buy_sell_enquiries')) {
            Schema::create('buy_sell_enquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('sender_phone')->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'replied', 'closed'])->default('pending');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            try {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            } catch (\Exception $e) {}
            $table->index(['item_id', 'status']);
            });
        }

        // Create buy_sell_favorites table if it doesn't exist
        if (!Schema::hasTable('buy_sell_favorites')) {
            Schema::create('buy_sell_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            try {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            } catch (\Exception $e) {}
            $table->unique(['item_id', 'user_id']);
            });
        }

        // Create buy_sell_reviews table if it doesn't exist
        if (!Schema::hasTable('buy_sell_reviews')) {
            Schema::create('buy_sell_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('reviewer_id');
            $table->unsignedInteger('reviewee_id'); // The seller
            $table->integer('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            try {
                $table->foreign('reviewer_id')->references('user_id')->on('users')->onDelete('cascade');
            } catch (\Exception $e) {}
            try {
                $table->foreign('reviewee_id')->references('user_id')->on('users')->onDelete('cascade');
            } catch (\Exception $e) {}
            $table->index(['item_id', 'status']);
            });
        }

        // Create buy_sell_analytics table if it doesn't exist
        if (!Schema::hasTable('buy_sell_analytics')) {
            Schema::create('buy_sell_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('contacts')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('shares')->default(0);
            $table->json('search_terms')->nullable();
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            $table->unique(['item_id', 'date']);
            });
        }

        // Create buy_sell_promotions table if it doesn't exist
        if (!Schema::hasTable('buy_sell_promotions')) {
            Schema::create('buy_sell_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->enum('promotion_type', ['promoted', 'featured', 'sponsored', 'network_boost']);
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('features')->nullable(); // What this promotion includes
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            $table->index(['promotion_type', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_sell_promotions');
        Schema::dropIfExists('buy_sell_analytics');
        Schema::dropIfExists('buy_sell_reviews');
        Schema::dropIfExists('buy_sell_favorites');
        Schema::dropIfExists('buy_sell_enquiries');
        Schema::dropIfExists('buy_sell_sellers');
        Schema::dropIfExists('buy_sell_videos');
        Schema::dropIfExists('buy_sell_images');
        Schema::dropIfExists('buy_sell_items');
        Schema::dropIfExists('buy_sell_categories');
    }
};
