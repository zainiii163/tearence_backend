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
        Schema::table('buy_sell_items', function (Blueprint $table) {
            // Drop existing foreign key constraint if it exists
            try {
                $table->dropForeign(['category_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            // Change category_id from unsignedBigInteger to uuid
            $table->dropColumn('category_id');
        });
        
        Schema::table('buy_sell_items', function (Blueprint $table) {
            // Add category_id as UUID
            $table->uuid('category_id')->nullable();
            
            // Add foreign key constraint to buysell_categories table (UUID)
            $table->foreign('category_id')->references('id')->on('buysell_categories')->onDelete('restrict');
            
            // Add index for performance
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buy_sell_items', function (Blueprint $table) {
            // Drop foreign key and UUID column
            try {
                $table->dropForeign(['category_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            $table->dropColumn('category_id');
        });
        
        Schema::table('buy_sell_items', function (Blueprint $table) {
            // Re-add category_id as unsignedBigInteger
            $table->unsignedBigInteger('category_id')->nullable();
            
            // Re-add foreign key to buy_sell_categories table (integer)
            $table->foreign('category_id')->references('id')->on('buy_sell_categories')->onDelete('restrict');
        });
    }
};
