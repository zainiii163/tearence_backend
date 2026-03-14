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
        Schema::create('business_affiliate_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('affiliate_category_id')->constrained('affiliate_categories')->onDelete('cascade');
            
            // Basic Information
            $table->string('business_name');
            $table->string('product_service_title');
            $table->string('tagline', 80)->nullable();
            $table->text('description');
            $table->string('country');
            $table->string('region')->nullable();
            
            // Offer Details
            $table->string('commission_type'); // 'percentage' or 'fixed'
            $table->decimal('commission_rate', 10, 2);
            $table->integer('cookie_duration'); // in days
            $table->json('allowed_traffic_types')->nullable(); // ['social_media', 'email', 'ppc', 'blogging', 'influencer', 'other']
            $table->text('restrictions')->nullable();
            
            // Links and Assets
            $table->string('tracking_link');
            $table->json('promotional_assets')->nullable(); // URLs to banners, images, videos, logos
            
            // Contact & Verification
            $table->string('business_email');
            $table->string('website_url')->nullable();
            $table->string('verification_document')->nullable(); // path to uploaded verification doc
            $table->boolean('is_verified')->default(false);
            
            // Status and Visibility
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            
            // Pricing
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Analytics
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('applications')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_active']);
            $table->index(['payment_status']);
            $table->index(['country']);
            $table->index(['is_promoted', 'is_featured', 'is_sponsored']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_affiliate_offers');
    }
};
