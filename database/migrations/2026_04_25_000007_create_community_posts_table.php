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
        Schema::create('community_posts', function (Blueprint $table) {
            $table->uuid('post_id')->primary();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->enum('post_type', ['ad_thread', 'discussion_thread'])->default('discussion_thread');
            
            // For ad_thread posts - link to existing advert tables
            $table->string('advert_type')->nullable(); // 'buy_sell', 'property', 'vehicle', 'job', 'service', 'event', 'funding', 'resorts_travel', 'banner', 'sponsored', 'affiliate', 'book'
            $table->uuid('advert_id')->nullable();
            
            // Common fields
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('media')->nullable(); // Array of images/videos
            
            // Engagement
            $table->integer('views_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('reactions_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('shares_count')->default(0);
            
            // Status and verification
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->text('flag_reason')->nullable();
            
            // Tags and categorization
            $table->json('tags')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')->references('category_id')->on('category')->onDelete('set null');
            
            // Location
            $table->string('location')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            
            // Discussion-specific fields
            $table->enum('discussion_type', ['general', 'question', 'review', 'advice', 'report'])->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['user_id', 'post_type']);
            $table->index(['post_type', 'is_pinned']);
            $table->index(['post_type', 'is_featured']);
            $table->index(['post_type', 'is_sponsored']);
            $table->index('category_id');
            $table->index('created_at');
            $table->index('views_count');
            $table->index('comments_count');
            $table->index('reactions_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_posts');
    }
};
