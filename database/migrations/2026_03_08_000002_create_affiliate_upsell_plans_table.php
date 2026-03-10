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
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->id();
            
            // Plan details
            $table->string('name', 100)->notNullable(); // Promoted, Featured, Sponsored
            $table->string('slug', 100)->unique()->notNullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->notNullable();
            $table->string('currency', 3)->default('GBP');
            $table->enum('duration_type', ['weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('duration_value')->default(1); // number of duration_type units
            
            // Visibility benefits
            $table->boolean('highlighted_background')->default(false);
            $table->boolean('appears_above_standard')->default(false);
            $table->integer('visibility_multiplier')->default(1); // 2x, 3x, etc.
            $table->boolean('top_of_category')->default(false);
            $table->boolean('larger_card_size')->default(false);
            $table->boolean('priority_search')->default(false);
            $table->boolean('homepage_placement')->default(false);
            $table->boolean('category_top_placement')->default(false);
            $table->boolean('homepage_slider')->default(false);
            $table->boolean('social_media_promotion')->default(false);
            $table->boolean('weekly_email_blast')->default(false);
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['price']);
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
