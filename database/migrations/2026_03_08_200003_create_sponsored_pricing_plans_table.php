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
        Schema::create('sponsored_pricing_plans', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('plan_id');
            $table->string('name', 100);
            $table->enum('tier', ['basic', 'plus', 'premium']);
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->integer('duration_days')->default(30);
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->json('visibility_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('badge_settings')->nullable();
            $table->json('placement_settings')->nullable();
            $table->json('promotion_settings')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tier', 'is_active']);
            $table->index(['sort_order']);
            $table->index(['price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_pricing_plans');
    }
};
