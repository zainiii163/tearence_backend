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
        Schema::table('listing', function (Blueprint $table) {
            // Venue-specific fields
            $table->string('venue_name')->nullable()->comment('Name of the venue');
            $table->string('venue_type')->nullable()->comment('conference_hall, banquet_hall, outdoor, restaurant, hotel, stadium, theater, gallery, community_center, other');
            $table->integer('capacity')->nullable()->comment('Maximum capacity of the venue');
            $table->string('country')->nullable()->comment('Country where venue is located');
            $table->decimal('price_per_hour', 10, 2)->nullable()->comment('Price per hour for venue rental');
            $table->decimal('price_per_day', 10, 2)->nullable()->comment('Price per day for venue rental');
            $table->json('facilities')->nullable()->comment('Available facilities: wifi, parking, projector, sound_system, catering, air_conditioning, wheelchair_accessible');
            $table->string('contact_email')->nullable()->comment('Contact email for venue inquiries');
            $table->string('contact_phone')->nullable()->comment('Contact phone number');
            $table->string('venue_website')->nullable()->comment('Venue website URL');
            
            // Indexes for performance
            $table->index(['venue_type', 'capacity']);
            $table->index('country');
            $table->index('price_per_hour');
            $table->index('price_per_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropIndex(['venue_type', 'capacity']);
            $table->dropIndex('country');
            $table->dropIndex('price_per_hour');
            $table->dropIndex('price_per_day');
            
            $table->dropColumn([
                'venue_name',
                'venue_type',
                'capacity',
                'country',
                'price_per_hour',
                'price_per_day',
                'facilities',
                'contact_email',
                'contact_phone',
                'venue_website'
            ]);
        });
    }
};
