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
        Schema::create('community_post_communities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('community_id');
            $table->foreign('community_id')->references('community_id')->on('communities')->onDelete('cascade');
            $table->uuid('post_id');
            $table->foreign('post_id')->references('post_id')->on('community_posts')->onDelete('cascade');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            $table->unique(['community_id', 'post_id']);
            $table->index('community_id');
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_post_communities');
    }
};
