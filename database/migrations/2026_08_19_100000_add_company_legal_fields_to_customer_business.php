<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_business', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_business', 'incorporation_date')) {
                $table->date('incorporation_date')->nullable()->after('vat_number');
            }
            if (! Schema::hasColumn('customer_business', 'duns_number')) {
                $table->string('duns_number', 32)->nullable()->after('incorporation_date');
            }
            if (! Schema::hasColumn('customer_business', 'postal_code')) {
                $table->string('postal_code', 32)->nullable()->after('country');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_business', function (Blueprint $table) {
            foreach (['incorporation_date', 'duns_number', 'postal_code'] as $col) {
                if (Schema::hasColumn('customer_business', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
