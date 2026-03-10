<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Remove constraint until properties table exists
            
            // Event Type
            $table->enum('event_type', [
                'view', 'inquiry', 'save', 'share', 'contact_agent', 
                'map_view', 'video_play', 'gallery_view', 'phone_click'
            ]);
            
            // User Information (nullable for anonymous users)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            
            // Additional Data
            $table->json('metadata')->nullable(); // Additional event-specific data
            
            // Timestamps
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['property_id', 'event_type']);
            $table->index(['created_at']);
            $table->index(['user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_analytics');
    }
};
