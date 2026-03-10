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
        Schema::create('affiliate_posts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->id();
            
            // Post type and basic info
            $table->enum('post_type', ['business', 'promoter'])->notNullable();
            $table->string('title', 200)->notNullable();
            $table->string('tagline', 80)->nullable();
            $table->text('description')->nullable();
            
            // Business-specific fields
            $table->string('business_name', 200)->nullable();
            $table->string('commission_rate', 50)->nullable(); // percentage or fixed amount
            $table->integer('cookie_duration')->nullable(); // days
            $table->json('allowed_traffic_types')->nullable(); // social, email, ppc, etc.
            $table->text('restrictions')->nullable();
            $table->string('affiliate_link', 500)->nullable();
            
            // Contact and verification
            $table->string('business_email', 200)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('verification_document', 500)->nullable();
            
            // Promoter-specific fields
            $table->string('target_audience', 100)->nullable();
            $table->json('hashtags')->nullable();
            
            // Common fields
            $table->string('country_region', 100)->nullable();
            $table->json('images')->nullable(); // Store multiple image paths
            $table->json('promotional_assets')->nullable(); // banners, videos, logos
            
            // Foreign keys (commented out until tables exist)
            // $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            // $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            
            // Alternative foreign key fields without constraints
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            
            // Upsell tier
            $table->enum('upsell_tier', ['standard', 'promoted', 'featured', 'sponsored'])->default('standard');
            
            // Status and timestamps
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->boolean('is_active')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['post_type', 'status']);
            $table->index(['upsell_tier', 'status']);
            $table->index(['customer_id']);
            $table->index(['category_id']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_posts');
    }
};
