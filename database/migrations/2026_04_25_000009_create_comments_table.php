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
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('comment_id')->primary();
            $table->uuid('post_id');
            $table->foreign('post_id')->references('post_id')->on('community_posts')->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->uuid('parent_id')->nullable(); // For nested comments
            $table->foreign('parent_id')->references('comment_id')->on('comments')->onDelete('cascade');
            
            $table->text('content');
            $table->enum('comment_type', ['question', 'review', 'tip', 'report_experience', 'general'])->default('general');
            
            // Engagement
            $table->integer('reactions_count')->default(0);
            $table->integer('replies_count')->default(0);
            
            // Moderation
            $table->boolean('is_flagged')->default(false);
            $table->text('flag_reason')->nullable();
            $table->boolean('is_hidden')->default(false);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['post_id', 'parent_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
