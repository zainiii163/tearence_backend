<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to safely convert the primary key
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id CHAR(36) NOT NULL FIRST');
        DB::statement('ALTER TABLE buy_sell_items DROP PRIMARY KEY');
        DB::statement('ALTER TABLE buy_sell_items ADD PRIMARY KEY (id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to bigint unsigned with auto_increment
        DB::statement('ALTER TABLE buy_sell_items DROP PRIMARY KEY');
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST');
        DB::statement('ALTER TABLE buy_sell_items ADD PRIMARY KEY (id)');
    }
};
