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
        Schema::create('sponsored_adverts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->foreignId('category_id')->constrained('sponsored_categories')->onDelete('cascade');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->json('images')->nullable();
            $table->string('video_url', 500)->nullable();
            $table->json('seller_info')->nullable();
            $table->json('location')->nullable();
            $table->bigInteger('views')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('reviews_count')->default(0);
            $table->boolean('featured')->default(false);
            $table->boolean('promoted')->default(false);
            $table->boolean('sponsored')->default(false);
            $table->enum('status', ['active', 'pending', 'expired', 'paused', 'rejected'])->default('pending');
            $table->enum('promotion_plan', ['free', 'promoted', 'featured', 'sponsored'])->default('free');
            $table->timestamp('promotion_expires_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['category_id']);
            $table->index(['country']);
            $table->index(['price']);
            $table->index(['created_at']);
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_adverts');
    }
};
