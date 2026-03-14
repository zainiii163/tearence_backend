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
        Schema::create('affiliate_analytics', function (Blueprint $table) {
            $table->id();
            
            // Polymorphic relationship to either business offers or user posts
            $table->morphs('affiliatable'); // affiliates_id, affiliates_type
            
            // Analytics Data
            $table->date('date');
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('unique_views')->default(0);
            $table->unsignedBigInteger('unique_clicks')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0.00);
            $table->decimal('revenue', 10, 2)->default(0.00);
            
            // Geographic Data
            $table->json('country_breakdown')->nullable(); // { "US": 100, "UK": 50 }
            $table->json('device_breakdown')->nullable(); // { "desktop": 60, "mobile": 40 }
            
            // Traffic Source Data
            $table->json('traffic_sources')->nullable(); // { "social": 30, "email": 20, "direct": 50 }
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate entries
            $table->unique(['affiliatable_type', 'affiliatable_id', 'date'], 'affiliate_analytics_unique');
            
            // Indexes
            $table->index(['date']);
            $table->index(['affiliatable_type', 'affiliatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_analytics');
    }
};
