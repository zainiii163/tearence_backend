<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clive: per-category money flow — Our money / Seller payouts / Other.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('category_money_flows')) {
            return;
        }

        Schema::create('category_money_flows', function (Blueprint $table) {
            $table->id();
            $table->string('category_key', 64)->index(); // jobs, books, buy-sell, affiliates…
            $table->string('bucket', 32)->index(); // platform | seller_payout | other
            $table->string('flow_subtype', 64)->index(); // product, fee, advert, commission, donation…
            $table->decimal('gross_amount', 14, 2)->default(0);
            $table->decimal('platform_amount', 14, 2)->default(0);
            $table->decimal('seller_amount', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_id', 191)->nullable()->index();
            $table->string('source_type', 64)->nullable()->index();
            $table->string('source_id', 64)->nullable()->index();
            $table->unsignedBigInteger('payer_user_id')->nullable()->index();
            $table->unsignedBigInteger('payee_user_id')->nullable()->index();
            $table->string('status', 32)->default('completed')->index();
            $table->string('description', 255)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();

            $table->unique(
                ['source_type', 'source_id', 'bucket', 'flow_subtype'],
                'cmf_source_bucket_subtype_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_money_flows');
    }
};
