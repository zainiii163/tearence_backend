<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('buy_sell_purchases')) {
            return;
        }

        // Ensure advert FK matches UUID PKs on buysell_adverts (char/varchar, not bigint)
        $column = collect(DB::select("SHOW COLUMNS FROM buy_sell_purchases LIKE 'buysell_advert_id'"))->first();
        $type = strtolower((string) ($column->Type ?? ''));
        if ($type && ! str_contains($type, 'char') && ! str_contains($type, 'varchar')) {
            DB::statement('ALTER TABLE buy_sell_purchases MODIFY buysell_advert_id VARCHAR(36) NOT NULL');
        }
    }

    public function down(): void
    {
        // no-op
    }
};
