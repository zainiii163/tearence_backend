<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('template_purchases')) {
            return;
        }

        Schema::table('template_purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('template_purchases', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('payment_method');
            }
            if (! Schema::hasColumn('template_purchases', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('template_purchases')) {
            return;
        }

        Schema::table('template_purchases', function (Blueprint $table) {
            foreach (['payment_id', 'paid_at'] as $column) {
                if (Schema::hasColumn('template_purchases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
