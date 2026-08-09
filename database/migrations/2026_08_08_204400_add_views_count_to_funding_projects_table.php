<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('funding_projects')) {
            return;
        }

        Schema::table('funding_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('funding_projects', 'views_count')) {
                $table->integer('views_count')->default(0);
            }
            if (!Schema::hasColumn('funding_projects', 'shares_count')) {
                $table->integer('shares_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('funding_projects')) {
            return;
        }

        Schema::table('funding_projects', function (Blueprint $table) {
            if (Schema::hasColumn('funding_projects', 'views_count')) {
                $table->dropColumn('views_count');
            }
            if (Schema::hasColumn('funding_projects', 'shares_count')) {
                $table->dropColumn('shares_count');
            }
        });
    }
};
