<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('business_affiliate_offers', 'postback_token')) {
                $table->string('postback_token', 64)->nullable()->unique()->after('tracking_link');
            }
        });

        // Backfill tokens for existing offers (merchant checkout webhooks)
        DB::table('business_affiliate_offers')
            ->whereNull('postback_token')
            ->orderBy('id')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('business_affiliate_offers')
                        ->where('id', $row->id)
                        ->update(['postback_token' => Str::random(40)]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (Schema::hasColumn('business_affiliate_offers', 'postback_token')) {
                $table->dropColumn('postback_token');
            }
        });
    }
};
