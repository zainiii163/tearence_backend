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
        Schema::create('community_follows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->uuid('community_id');
            $table->foreign('community_id')->references('community_id')->on('communities')->onDelete('cascade');
            $table->timestamp('followed_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['user_id', 'community_id']);
            $table->index('user_id');
            $table->index('community_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_follows');
    }
};
