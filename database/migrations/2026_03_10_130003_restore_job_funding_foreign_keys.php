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
        // Add foreign key constraints to jobs table
        Schema::table('jobs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_category_id')->references('id')->on('job_categories')->onDelete('cascade');
        });

        // Add foreign key constraints to job_seekers table
        Schema::table('job_seekers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to job_applications table
        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('job_seeker_id')->references('id')->on('job_seekers')->onDelete('cascade');
        });

        // Add foreign key constraints to funding_projects table
        Schema::table('funding_projects', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
        });

        // Add foreign key constraints to funding_backers table
        Schema::table('funding_backers', function (Blueprint $table) {
            $table->foreign('funding_project_id')->references('id')->on('funding_projects')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
            $table->foreign('funding_reward_id')->references('id')->on('funding_rewards')->onDelete('set null');
        });

        // Add foreign key constraints to funding_upsells table
        Schema::table('funding_upsells', function (Blueprint $table) {
            $table->foreign('funding_project_id')->references('id')->on('funding_projects')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
        });

        // Add foreign key constraints to promoted_adverts table
        Schema::table('promoted_adverts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to promoted_advert_favorites table
        Schema::table('promoted_advert_favorites', function (Blueprint $table) {
            $table->foreign('promoted_advert_id')->references('id')->on('promoted_adverts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Add foreign key constraints to promoted_advert_analytics table
        Schema::table('promoted_advert_analytics', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key constraints
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['job_category_id']);
        });

        Schema::table('job_seekers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['job_seeker_id']);
        });

        Schema::table('funding_projects', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('funding_backers', function (Blueprint $table) {
            $table->dropForeign(['funding_project_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['funding_reward_id']);
        });

        Schema::table('funding_upsells', function (Blueprint $table) {
            $table->dropForeign(['funding_project_id']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('promoted_adverts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('promoted_advert_favorites', function (Blueprint $table) {
            $table->dropForeign(['promoted_advert_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('promoted_advert_analytics', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
    }
};
