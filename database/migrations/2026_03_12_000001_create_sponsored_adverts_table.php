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
            $table->id();
            
            // Basic advert information
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description');
            $table->text('key_features')->nullable();
            $table->text('special_notes')->nullable();
            
            // Category and location
            $table->string('advert_type'); // product, service, property, job, event, vehicle, business_opportunity, miscellaneous
            $table->string('category');
            $table->string('country');
            $table->string('city')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->enum('condition', ['new', 'used', 'not_applicable'])->nullable();
            
            // Media
            $table->string('main_image');
            $table->json('additional_images')->nullable();
            $table->string('video_link')->nullable();
            
            // Seller information
            $table->string('seller_name');
            $table->string('business_name')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('website_url')->nullable();
            $table->string('logo_url')->nullable();
            $table->boolean('is_verified_seller')->default(false);
            
            // Location data
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('location_visibility', ['exact', 'approximate'])->default('exact');
            
            // Sponsored tier system
            $table->enum('sponsored_tier', ['basic', 'plus', 'premium'])->default('basic');
            $table->decimal('tier_price', 10, 2)->default(0);
            $table->date('promotion_start')->nullable();
            $table->date('promotion_end')->nullable();
            
            // Status and visibility
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'expired'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->unsignedBigInteger('saves_count')->default(0);
            $table->unsignedBigInteger('inquiries_count')->default(0);
            
            // Payment information
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // User relationships
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_active']);
            $table->index(['sponsored_tier', 'promotion_start', 'promotion_end']);
            $table->index(['country']);
            $table->index(['category']);
            $table->index(['advert_type']);
            $table->index(['payment_status']);
            $table->index(['views_count']);
            $table->index(['created_at']);
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
