<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (!Schema::hasColumn('business_affiliate_offers', 'join_instructions')) {
                $table->text('join_instructions')->nullable()->after('restrictions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('business_affiliate_offers', function (Blueprint $table) {
            if (Schema::hasColumn('business_affiliate_offers', 'join_instructions')) {
                $table->dropColumn('join_instructions');
            }
        });
    }
};
