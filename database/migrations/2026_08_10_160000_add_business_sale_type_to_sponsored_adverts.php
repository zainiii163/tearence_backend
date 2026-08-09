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
            if (!Schema::hasColumn('sponsored_adverts', 'business_sale_type')) {
                $table->string('business_sale_type', 32)->nullable()->after('advert_type');
            }
            if (!Schema::hasColumn('sponsored_adverts', 'business_sale_category')) {
                $table->string('business_sale_category', 64)->nullable()->after('business_sale_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sponsored_adverts')) {
            return;
        }

        Schema::table('sponsored_adverts', function (Blueprint $table) {
            if (Schema::hasColumn('sponsored_adverts', 'business_sale_category')) {
                $table->dropColumn('business_sale_category');
            }
            if (Schema::hasColumn('sponsored_adverts', 'business_sale_type')) {
                $table->dropColumn('business_sale_type');
            }
        });
    }
};
