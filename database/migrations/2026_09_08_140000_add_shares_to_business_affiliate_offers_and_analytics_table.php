<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('business_affiliate_offers', 'shares')) {
                $table->unsignedBigInteger('shares')->default(0)->after('clicks');
            }
        });

        Schema::table('affiliate_analytics', function (Blueprint $table) {
            if (!Schema::hasColumn('affiliate_analytics', 'shares')) {
                $table->unsignedBigInteger('shares')->default(0)->after('unique_clicks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (Schema::hasColumn('business_affiliate_offers', 'shares')) {
                $table->dropColumn('shares');
            }
        });

        Schema::table('affiliate_analytics', function (Blueprint $table) {
            if (Schema::hasColumn('affiliate_analytics', 'shares')) {
                $table->dropColumn('shares');
            }
        });
    }
};
