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
        Schema::create('user_affiliate_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('affiliate_category_id')->constrained('affiliate_categories')->onDelete('cascade');
            
            // Basic Information
            $table->string('title');
            $table->text('description');
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            
            // Affiliate Link and Image
            $table->string('affiliate_link');
            $table->string('image'); // path to uploaded image
            
            // Optional Enhancements
            $table->json('hashtags')->nullable();
            $table->string('target_audience')->nullable();
            
            // Status and Visibility
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            
            // Pricing
            $table->decimal('price', 10, 2)->default(0.00);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Analytics
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            
            // Moderation
            $table->text('moderation_notes')->nullable();
            $table->timestamp('moderated_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_active']);
            $table->index(['payment_status']);
            $table->index(['country']);
            $table->index(['is_promoted', 'is_featured', 'is_sponsored']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_affiliate_posts');
    }
};
