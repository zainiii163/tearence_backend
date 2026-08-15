<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Clive: Buy & Sell category "Dogs" / "Pets & Supplies" → "Animals & Pets".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('buy_sell_categories')) {
            return;
        }

        DB::table('buy_sell_categories')
            ->where('slug', 'pets-supplies')
            ->update([
                'name' => 'Animals & Pets',
                'description' => 'Animals, pets, pet supplies, and accessories',
            ]);

        DB::table('buy_sell_categories')
            ->where('slug', 'dogs')
            ->update([
                'name' => 'Animals & Pets',
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('buy_sell_categories')) {
            return;
        }

        DB::table('buy_sell_categories')
            ->where('slug', 'pets-supplies')
            ->update([
                'name' => 'Pets & Supplies',
                'description' => 'Pets, pet supplies, and animal accessories',
            ]);

        DB::table('buy_sell_categories')
            ->where('slug', 'dogs')
            ->update([
                'name' => 'Dogs',
            ]);
    }
};
