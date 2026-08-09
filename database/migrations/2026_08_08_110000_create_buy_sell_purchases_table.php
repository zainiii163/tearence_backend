<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('buy_sell_purchases')) {
            return;
        }

        Schema::create('buy_sell_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('buysell_advert_id', 36)->index();
            $table->unsignedBigInteger('buyer_id')->index();
            $table->unsignedBigInteger('seller_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 8)->default('USD');
            $table->decimal('fee_percent', 5, 2)->nullable();
            $table->decimal('platform_fee', 12, 2)->nullable();
            $table->decimal('seller_amount', 12, 2)->nullable();
            $table->string('payment_status', 32)->default('pending')->index();
            $table->string('payment_method', 32)->nullable();
            $table->string('payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('buyer_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_sell_purchases');
    }
};
