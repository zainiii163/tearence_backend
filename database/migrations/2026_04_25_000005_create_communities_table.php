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
        Schema::create('communities', function (Blueprint $table) {
            $table->uuid('community_id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->foreign('category_id')->references('category_id')->on('category')->onDelete('set null');
            $table->string('cover_image')->nullable();
            $table->enum('scope', ['global', 'region', 'city'])->default('global');
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->integer('members_count')->default(0);
            $table->integer('posts_count')->default(0);
            $table->integer('active_ads_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('strict_moderation')->default(false);
            $table->boolean('beginner_friendly')->default(false);
            $table->json('rules')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['slug', 'category_id', 'scope']);
            $table->index('members_count');
            $table->index('posts_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communities');
    }
};
