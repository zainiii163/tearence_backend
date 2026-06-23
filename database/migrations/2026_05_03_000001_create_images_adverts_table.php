<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('images_adverts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            
            // Image details
            $table->string('main_image');
            $table->json('images')->nullable();
            $table->string('thumbnail')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('orientation')->default('landscape'); // landscape, portrait, square
            $table->string('color_type')->default('color'); // color, black_white
            $table->string('dominant_color')->nullable();
            
            // Categories and tags
            $table->string('image_category')->nullable(); // Business, People, Nature, Food, Technology, Real Estate, Travel, Abstract
            $table->json('tags')->nullable();
            
            // Pricing and licensing
            $table->enum('license_type', ['standard', 'extended', 'editorial', 'exclusive'])->default('standard');
            $table->decimal('standard_price', 10, 2)->nullable();
            $table->decimal('extended_price', 10, 2)->nullable();
            $table->decimal('exclusive_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            
            // Resolution info
            $table->json('available_resolutions')->nullable();
            $table->json('available_formats')->nullable();
            
            // Verification status
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedInteger('verified_by')->nullable();
            
            // Contact info
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('business_name')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            
            // Model/property release
            $table->boolean('has_model_release')->default(false);
            $table->string('model_release_document')->nullable();
            $table->boolean('has_property_release')->default(false);
            $table->string('property_release_document')->nullable();
            
            // Stats
            $table->integer('views_count')->default(0);
            $table->integer('downloads_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            
            // Promotion
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'network_wide'])->default('standard');
            $table->boolean('is_verified_creator')->default(false);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['verification_status', 'is_active']);
            $table->index(['image_category', 'is_active']);
            $table->index(['license_type', 'is_active']);
            $table->index(['orientation', 'is_active']);
            $table->index(['promotion_tier', 'is_active']);
            $table->index(['standard_price']);
            $table->index(['downloads_count']);
            $table->index(['rating']);
            $table->fullText(['title', 'description']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('images_adverts');
    }
};
