<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persist marketplace sale splits on book/image purchases and add seller payout requests.
 * Buyer pays platform checkout → 15% WWA / 85% seller (ledger + payout).
 * Listing/ad fees remain 100% platform.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('book_advert_purchases')) {
            Schema::table('book_advert_purchases', function (Blueprint $table) {
                if (! Schema::hasColumn('book_advert_purchases', 'fee_percent')) {
                    $table->decimal('fee_percent', 5, 2)->nullable()->after('price_paid');
                }
                if (! Schema::hasColumn('book_advert_purchases', 'platform_fee')) {
                    $table->decimal('platform_fee', 12, 2)->nullable()->after('fee_percent');
                }
                if (! Schema::hasColumn('book_advert_purchases', 'seller_amount')) {
                    $table->decimal('seller_amount', 12, 2)->nullable()->after('platform_fee');
                }
                if (! Schema::hasColumn('book_advert_purchases', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->index()->after('book_id');
                }
            });
        }

        if (Schema::hasTable('image_advert_purchases')) {
            Schema::table('image_advert_purchases', function (Blueprint $table) {
                if (! Schema::hasColumn('image_advert_purchases', 'fee_percent')) {
                    $table->decimal('fee_percent', 5, 2)->nullable()->after('price_paid');
                }
                if (! Schema::hasColumn('image_advert_purchases', 'platform_fee')) {
                    $table->decimal('platform_fee', 12, 2)->nullable()->after('fee_percent');
                }
                if (! Schema::hasColumn('image_advert_purchases', 'seller_amount')) {
                    $table->decimal('seller_amount', 12, 2)->nullable()->after('platform_fee');
                }
                if (! Schema::hasColumn('image_advert_purchases', 'seller_id')) {
                    $table->unsignedBigInteger('seller_id')->nullable()->index()->after('image_id');
                }
            });
        }

        if (! Schema::hasTable('seller_marketplace_payouts')) {
            Schema::create('seller_marketplace_payouts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 3)->default('USD');
                $table->string('method', 32)->default('crypto');
                $table->string('status', 32)->default('pending')->index();
                $table->string('reference', 64)->nullable()->unique();
                $table->string('payout_details')->nullable();
                $table->text('notes')->nullable();
                $table->string('crypto_network', 32)->nullable();
                $table->string('crypto_address', 191)->nullable();
                $table->string('crypto_currency', 16)->nullable();
                $table->string('provider', 64)->nullable();
                $table->string('provider_payout_id')->nullable();
                $table->string('tx_hash')->nullable();
                $table->json('raw_webhook_json')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_marketplace_payouts');

        if (Schema::hasTable('book_advert_purchases')) {
            Schema::table('book_advert_purchases', function (Blueprint $table) {
                foreach (['fee_percent', 'platform_fee', 'seller_amount', 'seller_id'] as $col) {
                    if (Schema::hasColumn('book_advert_purchases', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('image_advert_purchases')) {
            Schema::table('image_advert_purchases', function (Blueprint $table) {
                foreach (['fee_percent', 'platform_fee', 'seller_amount', 'seller_id'] as $col) {
                    if (Schema::hasColumn('image_advert_purchases', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
