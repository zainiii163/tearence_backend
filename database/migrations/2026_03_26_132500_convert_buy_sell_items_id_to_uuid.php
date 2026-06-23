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
        // Drop auto_increment property and convert id to UUID
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->dropPrimary();
        });
        
        // Convert id column from bigint to char(36)
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id CHAR(36) NOT NULL');
        
        // Set primary key back
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->primary('id');
        });
        
        // Drop the auto_increment value if it exists
        DB::statement('ALTER TABLE buy_sell_items AUTO_INCREMENT = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop primary key
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->dropPrimary();
        });
        
        // Convert back to bigint unsigned with auto_increment
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        
        // Set primary key back
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->primary('id');
        });
    }
};
