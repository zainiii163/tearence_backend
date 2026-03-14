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
        Schema::create('buysell_adverts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Basic Information
            $table->string('title', 255);
            $table->text('description');
            $table->uuid('category_id');
            $table->uuid('subcategory_id')->nullable();
            $table->enum('condition', ['new', 'like_new', 'excellent', 'good', 'fair', 'poor']);
            $table->decimal('price', 12, 2);
            $table->boolean('negotiable')->default(false);
            $table->string('currency', 3)->default('USD');
            
            // Location
            $table->string('country', 100);
            $table->string('city', 100)->nullable();
            $table->string('state_province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Item Specifics
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('color', 50)->nullable();
            $table->text('dimensions')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('material', 100)->nullable();
            $table->string('usage_duration', 100)->nullable();
            $table->text('reason_for_selling')->nullable();
            
            // Seller Information
            $table->string('seller_name', 255);
            $table->string('seller_email', 255);
            $table->string('seller_phone', 50)->nullable();
            $table->string('seller_website', 255)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->boolean('verified_seller')->default(false);
            $table->boolean('show_phone')->default(false);
            $table->enum('preferred_contact', ['email', 'phone', 'website'])->default('email');
            
            // Media
            $table->json('images')->default('[]');
            $table->string('video_url', 500)->nullable();
            
            // Promotion
            $table->string('promotion_plan', 50)->nullable();
            $table->timestamp('promotion_start_date')->nullable();
            $table->timestamp('promotion_end_date')->nullable();
            $table->string('promotion_status', 20)->default('active');
            
            // Status
            $table->string('status', 20)->default('active');
            $table->boolean('featured')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_hot')->default(false);
            
            // Analytics
            $table->integer('views_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('contacts_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
            
            // Metadata
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
            $table->softDeletes();
            $table->integer('deleted_by')->nullable();
            
            // Foreign Keys - Commented out due to constraint issues, will add manually
            // $table->foreign('category_id')->references('id')->on('buysell_categories');
            // $table->foreign('subcategory_id')->references('id')->on('buysell_categories');
            // $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            // $table->foreign('deleted_by')->references('user_id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['category_id']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['is_promoted', 'promotion_start_date']);
            $table->index(['price']);
            $table->index(['country', 'city']);
            $table->index(['created_at']);
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buysell_adverts');
    }
};
