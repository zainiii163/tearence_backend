<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehicles')) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('vehicles', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('price');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vehicles') || ! Schema::hasColumn('vehicles', 'currency')) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
