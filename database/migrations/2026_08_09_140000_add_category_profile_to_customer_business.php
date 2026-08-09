<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Category-specific business profile fields (opening hours, booking, menu/services, etc.).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_business', function (Blueprint $table) {
            if (!Schema::hasColumn('customer_business', 'category_profile')) {
                $table->json('category_profile')->nullable()->after('business_category_slug');
            }
            if (!Schema::hasColumn('customer_business', 'booking_url')) {
                $table->string('booking_url', 500)->nullable()->after('business_website');
            }
            if (!Schema::hasColumn('customer_business', 'cover_image')) {
                $table->string('cover_image', 500)->nullable()->after('business_logo');
            }
            if (!Schema::hasColumn('customer_business', 'city')) {
                $table->string('city', 120)->nullable()->after('business_address');
            }
            if (!Schema::hasColumn('customer_business', 'country')) {
                $table->string('country', 120)->nullable()->after('city');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_business', function (Blueprint $table) {
            foreach (['category_profile', 'booking_url', 'cover_image', 'city', 'country'] as $col) {
                if (Schema::hasColumn('customer_business', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
