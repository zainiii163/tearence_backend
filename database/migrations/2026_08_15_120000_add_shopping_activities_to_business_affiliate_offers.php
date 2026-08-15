<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * YouTube Shopping-style activities on marketplace offers:
     * sale / price drop / discount code / scheduled product drop.
     * sale_price is the product price (table `price` stays the listing fee).
     */
    public function up(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (! Schema::hasColumn('business_affiliate_offers', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            }
            if (! Schema::hasColumn('business_affiliate_offers', 'compare_at_price')) {
                $table->decimal('compare_at_price', 10, 2)->nullable()->after('sale_price');
            }
            if (! Schema::hasColumn('business_affiliate_offers', 'discount_code')) {
                $table->string('discount_code', 64)->nullable()->after('compare_at_price');
            }
            if (! Schema::hasColumn('business_affiliate_offers', 'promotion_type')) {
                $table->string('promotion_type', 32)->nullable()->default('none')->after('discount_code');
            }
            if (! Schema::hasColumn('business_affiliate_offers', 'promotion_label')) {
                $table->string('promotion_label', 80)->nullable()->after('promotion_type');
            }
            if (! Schema::hasColumn('business_affiliate_offers', 'drop_at')) {
                $table->timestamp('drop_at')->nullable()->after('promotion_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            foreach (['sale_price', 'compare_at_price', 'discount_code', 'promotion_type', 'promotion_label', 'drop_at'] as $col) {
                if (Schema::hasColumn('business_affiliate_offers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
