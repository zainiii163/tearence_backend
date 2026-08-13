<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliate_payouts')) {
            return;
        }

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->index();
            $table->decimal('amount', 12, 2);
            $table->string('method', 50)->default('paypal');
            $table->string('payout_details')->nullable();
            $table->string('notes')->nullable();
            $table->string('status', 30)->default('pending')->index(); // pending|processing|paid|rejected
            $table->string('reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
