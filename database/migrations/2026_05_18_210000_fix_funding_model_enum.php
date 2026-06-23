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
        Schema::table('funding_projects', function (Blueprint $table) {
            $table->enum('funding_model', ['donation', 'reward', 'equity', 'loan', 'hybrid'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funding_projects', function (Blueprint $table) {
            $table->enum('funding_model', ['donation', 'reward_based', 'equity', 'loan_based'])->change();
        });
    }
};
