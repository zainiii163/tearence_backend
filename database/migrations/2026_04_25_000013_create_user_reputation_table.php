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
        Schema::create('user_reputation', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            
            // Reputation score
            $table->integer('reputation_score')->default(0);
            
            // Activity counts
            $table->integer('posts_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('communities_count')->default(0);
            
            // Quality metrics
            $table->integer('positive_reviews')->default(0);
            $table->integer('negative_reviews')->default(0);
            $table->integer('flags_received')->default(0);
            $table->integer('completed_deals')->default(0);
            
            // Badges
            $table->json('badges')->nullable();
            
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index('reputation_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reputation');
    }
};
