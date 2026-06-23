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
        // Add foreign key constraints to books table
        try {
            Schema::table('books', function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->foreign('author_id')->references('id')->on('authors')->onDelete('set null');
                $table->foreign('pricing_plan_id')->references('id')->on('ad_pricing_plans')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to book_saves table
        try {
            Schema::table('book_saves', function (Blueprint $table) {
                $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to book_upsells table
        try {
            Schema::table('book_upsells', function (Blueprint $table) {
                $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to authors table
        try {
            Schema::table('authors', function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to vehicles table
        try {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->foreign('vehicle_category_id')->references('id')->on('vehicle_categories')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to vehicle_favourites table
        try {
            Schema::table('vehicle_favourites', function (Blueprint $table) {
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to vehicle_analytics table
        try {
            Schema::table('vehicle_analytics', function (Blueprint $table) {
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        // Add foreign key constraints to vehicle_enquiries table
        try {
            Schema::table('vehicle_enquiries', function (Blueprint $table) {
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
                $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('books', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['author_id']);
            $table->dropForeign(['pricing_plan_id']);
        });

        Schema::table('book_saves', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('book_upsells', function (Blueprint $table) {
            $table->dropForeign(['book_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('authors', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['vehicle_category_id']);
        });

        Schema::table('vehicle_favourites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['vehicle_id']);
        });

        Schema::table('vehicle_analytics', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('vehicle_enquiries', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['user_id']);
        });
    }
};
