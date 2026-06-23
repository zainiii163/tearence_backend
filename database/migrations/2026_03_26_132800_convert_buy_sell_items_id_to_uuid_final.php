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
        // Check if buy_sell_promotions table exists and has foreign key to buy_sell_items
        $hasPromotionsFK = false;
        try {
            $result = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'buy_sell_promotions' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
            if ($result) {
                foreach ($result as $constraint) {
                    if (strpos($constraint->CONSTRAINT_NAME, 'item_id') !== false) {
                        $hasPromotionsFK = true;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error
        }
        
        // Drop foreign key from buy_sell_promotions if it exists
        if ($hasPromotionsFK) {
            try {
                Schema::table('buy_sell_promotions', function (Blueprint $table) {
                    $table->dropForeign(['item_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Convert item_id in buy_sell_promotions to UUID
            DB::statement('ALTER TABLE buy_sell_promotions MODIFY COLUMN item_id CHAR(36) NULL');
        }
        
        // Convert buy_sell_items.id to UUID
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id CHAR(36) NOT NULL');
        
        // Recreate foreign key if it existed
        if ($hasPromotionsFK) {
            Schema::table('buy_sell_promotions', function (Blueprint $table) {
                $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if buy_sell_promotions table exists and has foreign key
        $hasPromotionsFK = false;
        try {
            $result = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'buy_sell_promotions' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
            if ($result) {
                foreach ($result as $constraint) {
                    if (strpos($constraint->CONSTRAINT_NAME, 'item_id') !== false) {
                        $hasPromotionsFK = true;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist or other error
        }
        
        // Drop foreign key from buy_sell_promotions if it exists
        if ($hasPromotionsFK) {
            try {
                Schema::table('buy_sell_promotions', function (Blueprint $table) {
                    $table->dropForeign(['item_id']);
                });
            } catch (\Exception $e) {
                // Foreign key might not exist, continue
            }
            
            // Convert item_id back to bigint
            DB::statement('ALTER TABLE buy_sell_promotions MODIFY COLUMN item_id BIGINT UNSIGNED NULL');
        }
        
        // Convert buy_sell_items.id back to bigint
        DB::statement('ALTER TABLE buy_sell_items MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        
        // Recreate foreign key if it existed
        if ($hasPromotionsFK) {
            Schema::table('buy_sell_promotions', function (Blueprint $table) {
                $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            });
        }
    }
};
