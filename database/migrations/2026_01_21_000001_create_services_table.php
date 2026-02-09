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
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->string('title');
            $table->text('description');
            $table->enum('service_type', ['freelance', 'consulting', 'digital_service', 'local_service', 'online_service']);
            $table->enum('pricing_model', ['fixed_price', 'hourly_rate', 'package', 'quote_based']);
            $table->decimal('base_price', 10, 2);
            $table->enum('delivery_time', ['1_day', '3_days', '1_week', '2_weeks', '1_month', 'custom']);
            $table->enum('skill_level', ['beginner', 'intermediate', 'expert', 'professional']);
            $table->enum('service_category', ['design', 'writing', 'programming', 'marketing', 'business', 'video', 'audio', 'other']);
            $table->string('portfolio_link')->nullable();
            $table->json('requirements')->nullable();
            $table->integer('revisions_included')->default(0);
            $table->decimal('extra_fast_delivery', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('views_count')->default(0);
            $table->integer('orders_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('verified')->default(false);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('category')->onDelete('cascade');
            
            $table->index(['user_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['service_category', 'is_active']);
            $table->index(['featured', 'is_active']);
            $table->index(['verified', 'is_active']);
            $table->index(['rating', 'is_active']);
            $table->index(['orders_count', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
