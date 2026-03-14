<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pricing_plan_id')->constrained('job_pricing_plans')->onDelete('cascade');
            
            // Polymorphic relationship to the item being upsold
            $table->morphs('upsellable'); // job_listing or job_seeker
            
            // Upsell Details
            $table->string('upsell_type'); // promoted, featured, sponsored, network
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('duration_months')->default(1);
            
            // Status
            $table->string('status')->default('pending'); // pending, active, cancelled, expired
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Payment
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('payment_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['upsellable_type', 'upsellable_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_upsells');
    }
};
