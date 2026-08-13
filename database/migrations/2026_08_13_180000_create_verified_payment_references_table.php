<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotent ledger of verified payment IDs — blocks fake / reused payment_id confirms.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('verified_payment_references')) {
            return;
        }

        Schema::create('verified_payment_references', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id', 191)->unique();
            $table->string('provider', 32)->default('paypal'); // paypal|stripe|mock
            $table->string('status', 32)->default('completed');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('purchase_type', 64)->nullable()->index();
            $table->string('purchase_id', 64)->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamp('verified_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verified_payment_references');
    }
};
