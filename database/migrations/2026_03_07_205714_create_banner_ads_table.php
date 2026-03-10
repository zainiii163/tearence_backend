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
        Schema::create('banner_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('business_name');
            $table->string('contact_person')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('website_url')->nullable();
            $table->string('business_logo')->nullable();
            
            // Banner details
            $table->string('banner_type')->default('image'); // image, animated, html5, video
            $table->string('banner_size'); // 728x90, 300x250, etc.
            $table->string('banner_image');
            $table->string('destination_link');
            $table->string('call_to_action')->nullable();
            $table->text('key_selling_points')->nullable();
            $table->text('offer_details')->nullable();
            $table->date('validity_start')->nullable();
            $table->date('validity_end')->nullable();
            
            // Category and location
            $table->foreignId('banner_category_id'); // FK constraint removed due to migration order
            $table->string('country');
            $table->string('city')->nullable();
            $table->text('target_countries')->nullable();
            $table->text('target_audience')->nullable();
            
            // Upsell and promotion
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])->default('standard');
            $table->decimal('promotion_price', 10, 2)->default(0);
            $table->date('promotion_start')->nullable();
            $table->date('promotion_end')->nullable();
            $table->boolean('is_verified_business')->default(false);
            
            // Status and visibility
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'expired'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->timestamp('approved_at')->nullable();
            
            // User relationship
            $table->unsignedBigInteger('user_id')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_active']);
            $table->index(['promotion_tier', 'promotion_start', 'promotion_end']);
            $table->index(['banner_category_id']);
            $table->index(['country']);
            $table->index(['views_count']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_ads');
    }
};
