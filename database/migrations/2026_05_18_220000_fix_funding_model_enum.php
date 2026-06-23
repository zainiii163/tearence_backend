<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE funding_projects MODIFY COLUMN funding_model ENUM('donation', 'reward', 'equity', 'loan', 'hybrid')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE funding_projects MODIFY COLUMN funding_model ENUM('donation', 'reward_based', 'equity', 'loan_based')");
    }
};
