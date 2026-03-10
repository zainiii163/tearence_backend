<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_saved', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('property_id'); // Remove constraint until properties table exists
            $table->unsignedBigInteger('user_id');
            
            // Timestamps
            $table->timestamps();
            
            // Unique constraint to prevent duplicate saves
            $table->unique(['property_id', 'user_id'], 'property_saved_unique');
            
            // Indexes
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_saved');
    }
};
