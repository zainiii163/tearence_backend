<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events_venues_adverts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedBigInteger('category_id')->nullable();
            
            // Type: event or venue
            $table->enum('advert_type', ['event', 'venue'])->default('event');
            
            // Basic Information
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('tagline')->nullable();
            
            // Event-specific fields
            $table->date('event_date')->nullable();
            $table->time('event_time')->nullable();
            $table->date('event_end_date')->nullable();
            $table->time('event_end_time')->nullable();
            $table->string('venue_name')->nullable();
            $table->decimal('ticket_price', 10, 2)->nullable();
            $table->string('ticket_currency', 3)->default('USD');
            $table->boolean('free_event')->default(false);
            $table->string('event_category')->nullable(); // concerts, conferences, workshops, festivals, etc.
            
            // Venue-specific fields
            $table->string('venue_type')->nullable(); // wedding_venues, conference_centres, party_halls, etc.
            $table->integer('capacity')->nullable();
            $table->string('price_range')->nullable(); // e.g., "$500-$1000", "$1000-$5000"
            $table->json('amenities')->nullable(); // parking, catering, wifi, etc.
            
            // Common fields
            $table->string('country');
            $table->string('city');
            $table->string('state')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Contact Information
            $table->string('contact_name');
            $table->string('business_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();
            
            // Media
            $table->string('main_image')->nullable();
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->string('logo')->nullable();
            
            // Additional Details
            $table->json('key_features')->nullable();
            $table->text('additional_notes')->nullable();
            $table->boolean('indoor_outdoor')->nullable(); // true=indoor, false=outdoor
            $table->boolean('family_friendly')->default(false);
            $table->boolean('catering_available')->default(false);
            $table->boolean('parking_available')->default(false);
            $table->boolean('accessible')->default(false);
            
            // Promotion and Upsell
            $table->string('promotion_tier')->default('basic'); // basic, promoted, featured, sponsored, network_boost
            $table->decimal('promotion_price', 10, 2)->default(0);
            $table->timestamp('promotion_start')->nullable();
            $table->timestamp('promotion_expires')->nullable();
            $table->boolean('is_verified')->default(false);
            
            // Status
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'expired'])->default('pending');
            $table->boolean('is_active')->default(true);
            
            // Analytics
            $table->integer('views_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->integer('enquiries_count')->default(0);
            
            // Terms
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('accurate_info')->default(false);
            
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
            
            // Indexes
            $table->index(['advert_type', 'status']);
            $table->index(['country', 'city']);
            $table->index(['category_id', 'status']);
            $table->index(['promotion_tier', 'promotion_expires']);
            $table->index(['event_date', 'event_time']);
            $table->index('is_verified');
            $table->index('views_count');
            $table->fullText(['title', 'description', 'tagline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events_venues_adverts');
    }
};
