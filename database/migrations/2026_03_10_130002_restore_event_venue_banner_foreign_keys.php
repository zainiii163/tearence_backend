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
        // Add foreign key constraints to events table
        Schema::table('events', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null');
        });

        // Add foreign key constraints to venues table
        Schema::table('venues', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to venue_services table
        Schema::table('venue_services', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to event_venue_service table
        Schema::table('event_venue_service', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('venue_service_id')->references('id')->on('venue_services')->onDelete('cascade');
        });

        // Add foreign key constraints to banners table
        Schema::table('banners', function (Blueprint $table) {
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('banner_categories')->onDelete('cascade');
        });

        // Add foreign key constraints to banner_ads table
        Schema::table('banner_ads', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['venue_id']);
        });

        Schema::table('venues', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('venue_services', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('event_venue_service', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['venue_service_id']);
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['service_id']);
            $table->dropForeign(['category_id']);
        });

        Schema::table('banner_ads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
