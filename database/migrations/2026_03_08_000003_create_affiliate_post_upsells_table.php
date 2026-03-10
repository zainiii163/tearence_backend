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
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->id();
            
            // Foreign keys
            $table->foreignId('affiliate_post_id')->notNullable()->constrained('affiliate_posts')->onDelete('cascade');
            $table->foreignId('upsell_plan_id')->notNullable()->constrained('affiliate_upsell_plans')->onDelete('cascade');
            $table->foreignId('customer_id')->notNullable(); // Remove constraint until customers table exists
            
            // Payment details
            $table->decimal('amount_paid', 10, 2)->notNullable();
            $table->string('currency', 3)->default('GBP');
            $table->enum('payment_method', ['paypal', 'stripe', 'bank_transfer'])->notNullable();
            $table->string('transaction_id', 100)->notNullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            
            // Duration
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            // Status
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['affiliate_post_id', 'is_active']);
            $table->index(['customer_id', 'payment_status']);
            $table->index(['ends_at']);
            
            // Unique constraint to prevent duplicate active upsells for same post
            $table->unique(['affiliate_post_id', 'is_active'], 'unique_active_upsell_per_post');
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
