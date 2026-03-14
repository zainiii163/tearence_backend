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
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('service_provider_id')->nullable();
            $table->unsignedBigInteger('service_category_id');
            
            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->onDelete('set null');
            $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('tagline')->nullable();
            $table->longText('description');
            $table->json('whats_included')->nullable();
            $table->json('whats_not_included')->nullable();
            $table->text('requirements')->nullable();
            $table->enum('service_type', ['freelance', 'local', 'business'])->default('freelance');
            $table->decimal('starting_price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('delivery_time')->nullable(); // in days
            $table->json('availability')->nullable(); // days/hours
            $table->string('country');
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('service_area_radius')->nullable(); // for local services in km
            $table->integer('views')->default(0);
            $table->integer('enquiries')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->enum('status', ['draft', 'active', 'paused', 'suspended'])->default('draft');
            $table->enum('promotion_type', ['standard', 'promoted', 'featured', 'sponsored', 'network_boost'])->default('standard');
            $table->timestamp('promotion_expires_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->json('languages')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'promotion_type']);
            $table->index(['service_category_id', 'country']);
            $table->index(['service_type', 'status']);
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
