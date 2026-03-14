<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->enum('category', ['buy', 'rent', 'lease', 'auction', 'invest']);
            $table->enum('property_type', [
                'residential', 'commercial', 'industrial', 'land', 'agricultural', 
                'luxury', 'short_term_rental', 'investment', 'new_development'
            ]);
            $table->string('country');
            $table->string('city');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->boolean('negotiable')->default(false);
            $table->decimal('deposit', 15, 2)->nullable();
            $table->decimal('service_charges', 15, 2)->nullable();
            $table->decimal('maintenance_fees', 15, 2)->nullable();
            $table->string('cover_image');
            $table->json('additional_images')->nullable();
            $table->string('video_tour_link')->nullable();
            $table->text('description');
            $table->json('specifications')->nullable();
            $table->json('amenities')->nullable();
            $table->json('location_highlights')->nullable();
            $table->json('transport_links')->nullable();
            $table->string('seller_name');
            $table->string('seller_company')->nullable();
            $table->string('seller_phone');
            $table->string('seller_email');
            $table->string('seller_website')->nullable();
            $table->string('seller_logo')->nullable();
            $table->boolean('verified_agent')->default(false);
            $table->enum('advert_type', ['standard', 'promoted', 'featured', 'sponsored'])->default('standard');
            $table->timestamp('promoted_until')->nullable();
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('sponsored_until')->nullable();
            $table->integer('views')->default(0);
            $table->integer('saves')->default(0);
            $table->integer('enquiries')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('approved')->default(false);
            $table->timestamps();
            
            $table->index(['property_type', 'category']);
            $table->index(['country', 'city']);
            $table->index(['price', 'currency']);
            $table->index(['advert_type']);
            $table->index(['active', 'approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
