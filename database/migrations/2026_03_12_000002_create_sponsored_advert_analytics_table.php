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
        Schema::create('sponsored_advert_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsored_advert_id')->constrained('sponsored_adverts')->onDelete('cascade');
            
            // Tracking data
            $table->string('event_type'); // view, click, save, inquiry, share
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            
            // User tracking (optional)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Additional data
            $table->json('metadata')->nullable(); // Additional event data
            
            $table->timestamps();
            
            // Indexes
            $table->index(['sponsored_advert_id', 'event_type']);
            $table->index(['event_type']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_advert_analytics');
    }
};
