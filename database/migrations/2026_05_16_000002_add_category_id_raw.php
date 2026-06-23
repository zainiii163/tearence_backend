<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to avoid Schema Builder issues on production
        DB::statement('ALTER TABLE `customer_business` ADD COLUMN `category_id` INT(10) UNSIGNED NULL AFTER `customer_id`');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `customer_business` DROP COLUMN `category_id`');
    }
};
