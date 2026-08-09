<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sponsored_adverts')) {
            return;
        }

        Schema::table('sponsored_adverts', function (Blueprint $table) {
            if (!Schema::hasColumn('sponsored_adverts', 'company_number')) {
                $table->string('company_number', 100)->nullable()->after('business_name');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sponsored_adverts')) {
            return;
        }

        Schema::table('sponsored_adverts', function (Blueprint $table) {
            if (Schema::hasColumn('sponsored_adverts', 'company_number')) {
                $table->dropColumn('company_number');
            }
        });
    }
};