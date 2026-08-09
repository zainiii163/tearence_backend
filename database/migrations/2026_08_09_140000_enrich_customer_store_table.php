<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_store')) {
            return;
        }

        Schema::table('customer_store', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_store', 'category')) {
                $table->string('category', 100)->nullable()->after('status')->index();
            }
            if (!Schema::hasColumn('customer_store', 'description')) {
                $table->text('description')->nullable()->after('category');
            }
            if (!Schema::hasColumn('customer_store', 'phone')) {
                $table->string('phone', 50)->nullable()->after('description');
            }
            if (!Schema::hasColumn('customer_store', 'email')) {
                $table->string('email', 255)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('customer_store', 'website')) {
                $table->string('website', 500)->nullable()->after('email');
            }
            if (!Schema::hasColumn('customer_store', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('website');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customer_store')) {
            return;
        }

        Schema::table('customer_store', function (Blueprint $table) {
            foreach (['category', 'description', 'phone', 'email', 'website', 'is_featured'] as $col) {
                if (Schema::hasColumn('customer_store', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
