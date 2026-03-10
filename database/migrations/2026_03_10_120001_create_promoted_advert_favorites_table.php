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
        Schema::create('promoted_advert_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promoted_advert_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();
            
            // Prevent duplicates
            $table->unique(['promoted_advert_id', 'user_id'], 'unique_promoted_favorite');
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['promoted_advert_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoted_advert_favorites');
    }
};
