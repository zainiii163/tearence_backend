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
        Schema::create('affiliate_upsell_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'Promoted', 'Featured', 'Sponsored'
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('duration_type'); // 'weekly', 'monthly', 'yearly'
            $table->integer('duration_days');
            
            // Features
            $table->json('features'); // Array of features/benefits
            $table->text('benefits')->nullable();
            
            // Visibility Settings
            $table->boolean('highlighted_background')->default(false);
            $table->boolean('above_standard_posts')->default(false);
            $table->boolean('top_category_placement')->default(false);
            $table->boolean('larger_card_size')->default(false);
            $table->boolean('priority_search')->default(false);
            $table->boolean('homepage_placement')->default(false);
            $table->boolean('category_top_placement')->default(false);
            $table->boolean('homepage_slider')->default(false);
            $table->boolean('social_media_promotion')->default(false);
            $table->boolean('email_blast_inclusion')->default(false);
            
            // Badge Settings
            $table->string('badge_text')->nullable();
            $table->string('badge_color')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'sort_order']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_upsell_plans');
    }
};
