<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            if (!Schema::hasColumn('customer', 'user_type')) {
                $table->string('user_type', 20)->default('basic')->after('password_hash');
            }
            if (!Schema::hasColumn('customer', 'phone_verified_at')) {
                $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            }
            if (!Schema::hasColumn('customer', 'country')) {
                $table->string('country', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('customer', 'city')) {
                $table->string('city', 100)->nullable()->after('country');
            }
            if (!Schema::hasColumn('customer', 'business_category')) {
                $table->string('business_category', 100)->nullable()->after('city');
            }
        });

        Schema::table('customer_business', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_business', 'vat_number')) {
                $table->string('vat_number', 50)->nullable()->after('business_company_registration');
            }
            if (!Schema::hasColumn('customer_business', 'business_category_slug')) {
                $table->string('business_category_slug', 100)->nullable()->after('category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $columns = ['user_type', 'phone_verified_at', 'country', 'city', 'business_category'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('customer', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('customer_business', function (Blueprint $table) {
            foreach (['vat_number', 'business_category_slug'] as $column) {
                if (Schema::hasColumn('customer_business', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
