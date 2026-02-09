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
        Schema::create('revenue_tracking', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('revenue_id');
            $table->enum('revenue_type', ['job_upsell', 'candidate_upsell']); // Type of revenue
            $table->integer('related_id'); // ID of job_upsell or candidate_upsell
            $table->unsignedInteger('customer_id'); // Customer who made the payment
            $table->string('upsell_type', 50); // featured, suggested, featured_profile, job_alerts_boost
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_method', 50)->nullable(); // paypal, stripe, etc.
            $table->string('payment_transaction_id', 255)->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->dateTime('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')
                ->references('customer_id')->on('customer')
                ->onDelete('cascade');

            // Indexes
            $table->index('revenue_type');
            $table->index('customer_id');
            $table->index('payment_status');
            $table->index('payment_date');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_tracking');
    }
};

