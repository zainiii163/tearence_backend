<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('plan_id');
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('payment_id')->nullable(); // Stripe/PayPal payment reference
            $table->string('payment_method')->nullable();
            $table->decimal('amount_paid', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('subscription_plans');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
