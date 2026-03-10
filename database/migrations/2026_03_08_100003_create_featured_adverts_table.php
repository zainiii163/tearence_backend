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
        Schema::create('featured_adverts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('id');
            $table->unsignedInteger('listing_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->string('advert_type', 50); // product, service, property, job, event, vehicle, etc.
            $table->string('condition', 20)->nullable(); // new, used, refurbished
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            $table->string('country');
            $table->string('city');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('upsell_tier', 20); // promoted, featured, sponsored
            $table->decimal('upsell_price', 8, 2);
            $table->string('payment_status', 20)->default('pending'); // pending, paid, failed
            $table->string('payment_reference')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->boolean('is_active')->default(true);
            $table->integer('view_count')->default(0);
            $table->integer('save_count')->default(0);
            $table->integer('contact_count')->default(0);
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('review_count')->default(0);
            $table->boolean('is_verified_seller')->default(false);
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['listing_id']);
            $table->index(['customer_id']);
            $table->index(['category_id']);
            $table->index(['country_id']);
            $table->index(['upsell_tier']);
            $table->index(['is_active']);
            $table->index(['expires_at']);
            $table->index(['country']);
            $table->index(['city']);
            $table->index(['created_at']);
            
            // Foreign keys
            $table->foreign('listing_id')->references('listing_id')->on('listing')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('category')->onDelete('set null');
            $table->foreign('country_id')->references('country_id')->on('country')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_adverts');
    }
};
