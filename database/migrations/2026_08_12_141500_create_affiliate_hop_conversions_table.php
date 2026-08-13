<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Durable conversion ledger for hop-based affiliate attributions (Ahrefs model).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('affiliate_hop_conversions')) {
            return;
        }

        Schema::create('affiliate_hop_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_application_id')->index();
            $table->unsignedBigInteger('business_affiliate_offer_id')->nullable()->index();
            $table->string('tracking_code', 64)->index();
            $table->string('order_id', 120)->nullable();
            $table->decimal('sale_amount', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->string('commission_type', 32)->nullable();
            $table->decimal('commission_rate', 10, 2)->nullable();
            $table->string('status', 32)->default('confirmed')->index();
            $table->string('attributed_via', 16)->default('code'); // cookie|code
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(
                ['business_affiliate_offer_id', 'order_id'],
                'aff_hop_conv_offer_order_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_hop_conversions');
    }
};
