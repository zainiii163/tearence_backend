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
        // Add foreign key constraints to services table
        Schema::table('services', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_provider_id')->references('id')->on('service_providers')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('service_categories')->onDelete('cascade');
        });

        // Add foreign key constraints to service_packages table
        Schema::table('service_packages', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_media table
        Schema::table('service_media', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_locations table
        Schema::table('service_locations', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_activities table
        Schema::table('service_activities', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_saved table
        Schema::table('service_saved', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_upsells table
        Schema::table('service_upsells', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_providers table
        Schema::table('service_providers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to service_addons table
        Schema::table('service_addons', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });

        // Add foreign key constraints to service_promotions table
        Schema::table('service_promotions', function (Blueprint $table) {
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['service_provider_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_media', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_locations', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_activities', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_saved', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_upsells', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('service_addons', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('service_promotions', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });
    }
};
