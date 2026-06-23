<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('affiliate_upsell_plans', function (Blueprint $table) {
            $table->boolean('top_category_placement')->after('above_standard_posts')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_upsell_plans', function (Blueprint $table) {
            $table->dropColumn('top_category_placement');
        });
    }
};
