<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('job_seekers')) {
            return;
        }

        Schema::table('job_seekers', function (Blueprint $table) {
            $string = function (string $name, int $length = 255, bool $nullable = true) use ($table) {
                if (!Schema::hasColumn('job_seekers', $name)) {
                    $nullable
                        ? $table->string($name, $length)->nullable()
                        : $table->string($name, $length);
                }
            };

            $text = function (string $name, bool $nullable = true) use ($table) {
                if (!Schema::hasColumn('job_seekers', $name)) {
                    $nullable ? $table->text($name)->nullable() : $table->text($name);
                }
            };

            $boolean = function (string $name, bool $default = false) use ($table) {
                if (!Schema::hasColumn('job_seekers', $name)) {
                    $table->boolean($name)->default($default);
                }
            };

            $string('title', 200);
            $string('profession');
            $string('full_name');
            $text('bio');
            $string('profile_photo');
            $string('profile_photo_url');
            $string('cv_file');
            $string('cv_file_url');
            $string('portfolio_link', 500);
            $string('linkedin_url', 500);
            $string('linkedin_link', 500);
            $string('github_url', 500);
            $string('github_link', 500);
            $string('website_url', 500);
            $string('experience_level', 50);
            $string('education_level', 50);
            $text('education_details');
            $text('experience_summary');
            $text('key_skills');
            $text('desired_role');
            $text('industries_interested');

            if (!Schema::hasColumn('job_seekers', 'years_of_experience')) {
                $table->integer('years_of_experience')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'salary_expectation_min')) {
                $table->decimal('salary_expectation_min', 12, 2)->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'salary_expectation_max')) {
                $table->decimal('salary_expectation_max', 12, 2)->nullable();
            }

            $string('salary_expectation', 100);
            $string('salary_currency', 3);
            $string('preferred_work_type', 50);
            $string('work_type_preference', 50);
            $string('country', 100);
            $string('city', 100);
            $string('state', 100);
            $text('address');
            $string('location_name');

            if (!Schema::hasColumn('job_seekers', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }

            $boolean('is_remote_available', true);
            $boolean('remote_availability');
            $boolean('willing_to_relocate');
            $boolean('is_active', true);
            $boolean('is_featured');
            $boolean('is_sponsored');
            $boolean('is_promoted');
            $boolean('terms_accepted');
            $boolean('accurate_info');
            $boolean('verified_profile');

            if (!Schema::hasColumn('job_seekers', 'status')) {
                $table->string('status', 50)->default('active');
            }

            if (!Schema::hasColumn('job_seekers', 'views_count')) {
                $table->integer('views_count')->default(0);
            }

            if (!Schema::hasColumn('job_seekers', 'views')) {
                $table->integer('views')->default(0);
            }

            if (!Schema::hasColumn('job_seekers', 'profile_contacts_count')) {
                $table->integer('profile_contacts_count')->default(0);
            }

            if (!Schema::hasColumn('job_seekers', 'contact_count')) {
                $table->integer('contact_count')->default(0);
            }

            if (!Schema::hasColumn('job_seekers', 'saves_count')) {
                $table->integer('saves_count')->default(0);
            }

            if (!Schema::hasColumn('job_seekers', 'featured_until')) {
                $table->timestamp('featured_until')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'sponsored_until')) {
                $table->timestamp('sponsored_until')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'promoted_until')) {
                $table->timestamp('promoted_until')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'last_contact_at')) {
                $table->timestamp('last_contact_at')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'preferred_locations')) {
                $table->json('preferred_locations')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'preferred_industries')) {
                $table->json('preferred_industries')->nullable();
            }

            if (!Schema::hasColumn('job_seekers', 'additional_links')) {
                $table->json('additional_links')->nullable();
            }
        });

        if (!Schema::hasTable('job_applications')) {
            Schema::create('job_applications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('job_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('job_seeker_id')->nullable();
                $table->text('cover_letter')->nullable();
                $table->string('cv_file', 255)->nullable();
                $table->string('portfolio_link', 500)->nullable();
                $table->string('contact_email', 255)->nullable();
                $table->string('contact_phone', 50)->nullable();
                $table->string('status', 50)->default('submitted');
                $table->text('employer_notes')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamp('viewed_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Non-destructive migration.
    }
};
