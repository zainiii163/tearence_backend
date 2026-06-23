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
            // Add missing status columns
            if (!Schema::hasColumn('funding_projects', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('funding_projects', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (!Schema::hasColumn('funding_projects', 'is_sponsored')) {
                $table->boolean('is_sponsored')->default(false);
            }
            if (!Schema::hasColumn('funding_projects', 'is_promoted')) {
                $table->boolean('is_promoted')->default(false);
            }
            if (!Schema::hasColumn('funding_projects', 'is_verified')) {
                $table->boolean('is_verified')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funding_projects', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_featured', 'is_sponsored', 'is_promoted', 'is_verified']);
        });
    }
};
