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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('project_type', 50);
            $table->string('title');
            $table->text('tagline')->nullable();
            $table->text('description')->nullable();
            $table->text('story')->nullable();
            $table->text('vision')->nullable();
            $table->string('funding_model', 50); // 'donation', 'reward', 'equity', 'loan'
            $table->decimal('funding_goal', 12, 2);
            $table->decimal('current_funding', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('status', 50)->default('draft'); // 'draft', 'active', 'completed', 'cancelled'
            $table->string('promotion_tier', 50)->default('basic');
            $table->timestamp('submitted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['promotion_tier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
