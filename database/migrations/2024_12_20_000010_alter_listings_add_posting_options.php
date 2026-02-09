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
        Schema::table('listing', function (Blueprint $table) {
            // Additional posting options as per requirements
            $table->boolean('is_paid')->default(false)->after('is_suggested');
            $table->boolean('is_promoted')->default(false)->after('is_paid');
            $table->boolean('is_sponsored')->default(false)->after('is_promoted');
            $table->boolean('is_business')->default(false)->after('is_sponsored');
            $table->boolean('is_store')->default(false)->after('is_business');
            
            // Expiry dates for each option
            $table->dateTime('paid_expires_at')->nullable()->after('is_paid');
            $table->dateTime('promoted_expires_at')->nullable()->after('is_promoted');
            $table->dateTime('sponsored_expires_at')->nullable()->after('is_sponsored');
            $table->dateTime('business_expires_at')->nullable()->after('is_business');
            $table->dateTime('store_expires_at')->nullable()->after('is_store');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid',
                'is_promoted',
                'is_sponsored',
                'is_business',
                'is_store',
                'paid_expires_at',
                'promoted_expires_at',
                'sponsored_expires_at',
                'business_expires_at',
                'store_expires_at',
            ]);
        });
    }
};

