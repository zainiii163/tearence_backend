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
        // Drop foreign keys that reference buy_sell_items.id
        $foreignKeys = [
            'buy_sell_images_item_id_foreign',
            'buy_sell_videos_item_id_foreign', 
            'buy_sell_sellers_item_id_foreign',
            'buy_sell_enquiries_item_id_foreign',
            'buy_sell_favorites_item_id_foreign',
            'buy_sell_reviews_item_id_foreign',
            'buy_sell_analytics_item_id_foreign',
            'buy_sell_promotions_item_id_foreign'
        ];
        
        foreach ($foreignKeys as $foreignKey) {
            try {
                Schema::table('buy_sell_items', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign([$foreignKey]);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Drop foreign keys from related tables
        $relatedTables = [
            'buy_sell_images' => 'item_id',
            'buy_sell_videos' => 'item_id',
            'buy_sell_sellers' => 'item_id', 
            'buy_sell_enquiries' => 'item_id',
            'buy_sell_favorites' => 'item_id',
            'buy_sell_reviews' => 'item_id',
            'buy_sell_analytics' => 'item_id',
            'buy_sell_promotions' => 'item_id'
        ];
        
        foreach ($relatedTables as $table => $column) {
            try {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Convert buy_sell_items.id to UUID
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id CHAR(36) NOT NULL FIRST');
        
        // Convert all foreign key columns to UUID
        foreach ($relatedTables as $table => $column) {
            DB::statement("ALTER TABLE {$table} MODIFY COLUMN {$column} CHAR(36) NULL");
        }
        
        // Recreate foreign keys
        foreach ($relatedTables as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->foreign($column)->references('id')->on('buy_sell_items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys
        $relatedTables = [
            'buy_sell_images' => 'item_id',
            'buy_sell_videos' => 'item_id',
            'buy_sell_sellers' => 'item_id', 
            'buy_sell_enquiries' => 'item_id',
            'buy_sell_favorites' => 'item_id',
            'buy_sell_reviews' => 'item_id',
            'buy_sell_analytics' => 'item_id',
            'buy_sell_promotions' => 'item_id'
        ];
        
        foreach ($relatedTables as $table => $column) {
            try {
                Schema::table($table, function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
        }
        
        // Convert back to bigint
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT FIRST');
        
        foreach ($relatedTables as $table => $column) {
            DB::statement("ALTER TABLE {$table} MODIFY COLUMN {$column} BIGINT UNSIGNED NULL");
        }
        
        // Recreate foreign keys
        foreach ($relatedTables as $table => $column) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->foreign($column)->references('id')->on('buy_sell_items')->onDelete('cascade');
            });
        }
    }
};
