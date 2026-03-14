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
        Schema::create('sponsored_advert_favourites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsored_advert_id')->constrained('sponsored_adverts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate favourites
            $table->unique(['sponsored_advert_id', 'user_id']);
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['sponsored_advert_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_advert_favourites');
    }
};
