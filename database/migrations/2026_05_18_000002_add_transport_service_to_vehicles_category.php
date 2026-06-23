<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE vehicles_adverts MODIFY COLUMN category ENUM('sale', 'hire', 'lease', 'transport_service')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE vehicles_adverts MODIFY COLUMN category ENUM('sale', 'hire', 'lease')");
    }
};
