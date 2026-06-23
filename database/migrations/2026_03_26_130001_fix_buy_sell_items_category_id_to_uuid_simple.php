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
        // Direct SQL approach to avoid foreign key issues
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN category_id CHAR(36) NULL');
        
        // Add foreign key constraint to buysell_categories table (UUID)
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('buysell_categories')->onDelete('restrict');
        });
        
        // Add index for performance
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key if it exists
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
        
        // Convert back to unsignedBigInteger
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN category_id BIGINT UNSIGNED NULL');
        
        // Re-add foreign key to buy_sell_categories table (integer) if needed
        Schema::table('buy_sell_items', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('buy_sell_categories')->onDelete('restrict');
        });
    }
};
