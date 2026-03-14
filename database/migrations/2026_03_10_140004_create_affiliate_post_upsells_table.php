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
        Schema::create('affiliate_post_upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('affiliate_upsell_plan_id')->constrained('affiliate_upsell_plans')->onDelete('cascade');
            
            // Polymorphic relationship to either business offers or user posts
            $table->morphs('affiliatable'); // affiliates_id, affiliates_type
            
            // Payment Information
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->string('payment_method')->nullable(); // 'paypal', 'stripe', etc.
            $table->timestamp('paid_at')->nullable();
            
            // Duration
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true);
            
            // Admin Notes
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['payment_status', 'is_active']);
            $table->index(['expires_at']);
            $table->index(['affiliatable_type', 'affiliatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_post_upsells');
    }
};
