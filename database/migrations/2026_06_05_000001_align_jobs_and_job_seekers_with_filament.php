<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $columns = [
                'company_description' => fn () => $table->text('company_description')->nullable(),
                'company_size' => fn () => $table->string('company_size', 50)->nullable(),
                'company_industry' => fn () => $table->string('company_industry')->nullable(),
                'company_founded' => fn () => $table->string('company_founded', 20)->nullable(),
                'company_social' => fn () => $table->json('company_social')->nullable(),
                'state' => fn () => $table->string('state', 100)->nullable(),
                'address' => fn () => $table->text('address')->nullable(),
                'education_level' => fn () => $table->string('education_level', 50)->nullable(),
                'application_phone' => fn () => $table->string('application_phone', 50)->nullable(),
                'application_instructions' => fn () => $table->text('application_instructions')->nullable(),
                'terms_accepted' => fn () => $table->boolean('terms_accepted')->default(false),
                'accurate_info' => fn () => $table->boolean('accurate_info')->default(false),
                'salary_range' => fn () => $table->string('salary_range', 100)->nullable(),
            ];

            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('jobs', $name)) {
                    $definition();
                }
            }
        });

        Schema::table('job_seekers', function (Blueprint $table) {
            $columns = [
                'title' => fn () => $table->string('title', 200)->nullable(),
                'bio' => fn () => $table->text('bio')->nullable(),
                'profile_photo' => fn () => $table->string('profile_photo', 500)->nullable(),
                'cv_file' => fn () => $table->string('cv_file', 500)->nullable(),
                'portfolio_link' => fn () => $table->string('portfolio_link', 500)->nullable(),
                'website_url' => fn () => $table->string('website_url', 500)->nullable(),
                'experience_level' => fn () => $table->string('experience_level', 50)->nullable(),
                'years_of_experience' => fn () => $table->integer('years_of_experience')->nullable(),
                'education_level' => fn () => $table->string('education_level', 50)->nullable(),
                'key_skills' => fn () => $table->text('key_skills')->nullable(),
                'desired_role' => fn () => $table->text('desired_role')->nullable(),
                'industries_interested' => fn () => $table->text('industries_interested')->nullable(),
                'salary_expectation_min' => fn () => $table->decimal('salary_expectation_min', 12, 2)->nullable(),
                'salary_expectation_max' => fn () => $table->decimal('salary_expectation_max', 12, 2)->nullable(),
                'salary_currency' => fn () => $table->string('salary_currency', 3)->default('USD'),
                'preferred_work_type' => fn () => $table->string('preferred_work_type', 50)->nullable(),
                'location_name' => fn () => $table->string('location_name', 255)->nullable(),
                'willing_to_relocate' => fn () => $table->boolean('willing_to_relocate')->default(false),
            ];

            foreach ($columns as $name => $definition) {
                if (!Schema::hasColumn('job_seekers', $name)) {
                    $definition();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $drop = [
                'company_description', 'company_size', 'company_industry', 'company_founded',
                'company_social', 'state', 'address', 'education_level', 'application_phone',
                'application_instructions', 'terms_accepted', 'accurate_info', 'salary_range',
            ];
            foreach ($drop as $column) {
                if (Schema::hasColumn('jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('job_seekers', function (Blueprint $table) {
            $drop = [
                'title', 'bio', 'profile_photo', 'cv_file', 'portfolio_link', 'website_url',
                'experience_level', 'years_of_experience', 'education_level', 'key_skills',
                'desired_role', 'industries_interested', 'salary_expectation_min',
                'salary_expectation_max', 'salary_currency', 'preferred_work_type',
                'location_name', 'willing_to_relocate',
            ];
            foreach ($drop as $column) {
                if (Schema::hasColumn('job_seekers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
