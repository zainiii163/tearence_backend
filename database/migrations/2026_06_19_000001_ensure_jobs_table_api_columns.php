<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jobs')) {
            return;
        }

        Schema::table('jobs', function (Blueprint $table) {
            $addString = function (string $name, int $length = 255, bool $nullable = true) use ($table) {
                if (!Schema::hasColumn('jobs', $name)) {
                    $nullable
                        ? $table->string($name, $length)->nullable()
                        : $table->string($name, $length);
                }
            };

            $addText = function (string $name, bool $nullable = true) use ($table) {
                if (!Schema::hasColumn('jobs', $name)) {
                    $nullable
                        ? $table->text($name)->nullable()
                        : $table->text($name);
                }
            };

            $addBoolean = function (string $name, bool $default = false) use ($table) {
                if (!Schema::hasColumn('jobs', $name)) {
                    $table->boolean($name)->default($default);
                }
            };

            $addDecimal = function (string $name) use ($table) {
                if (!Schema::hasColumn('jobs', $name)) {
                    $table->decimal($name, 12, 2)->nullable();
                }
            };

            if (!Schema::hasColumn('jobs', 'job_category_id') && !Schema::hasColumn('jobs', 'category_id')) {
                $table->unsignedBigInteger('job_category_id')->nullable();
            }

            $addString('slug', 250);
            $addText('responsibilities');
            $addText('requirements');
            $addText('skills_needed');
            $addText('benefits');
            $addText('company_description');
            $addString('company_size', 50);
            $addString('company_industry');
            $addString('company_founded', 20);
            $addString('company_logo');
            $addString('logo_url');
            $addString('company_website');
            $addString('contact_email');
            $addString('application_email');
            $addString('application_link', 500);
            $addString('application_phone', 50);
            $addText('application_instructions');
            $addString('application_website', 500);
            $addString('state', 100);
            $addText('address');
            $addString('location_name');
            $addString('work_type', 50, false);
            $addString('experience_level', 50, false);
            $addString('education_level', 50);
            $addString('salary_range', 100);
            $addDecimal('salary_min');
            $addDecimal('salary_max');
            $addString('salary_currency', 3);
            $addString('currency', 3);
            $addString('application_method', 50);

            if (!Schema::hasColumn('jobs', 'company_social')) {
                $table->json('company_social')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable();
            }

            if (!Schema::hasColumn('jobs', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable();
            }

            $addBoolean('is_remote');
            $addBoolean('remote_available');
            $addBoolean('is_verified_employer');
            $addBoolean('verified_employer');
            $addBoolean('terms_accepted');
            $addBoolean('accurate_info');
            $addBoolean('is_active', true);
            $addBoolean('is_urgent');
            $addBoolean('is_featured');
            $addBoolean('is_sponsored');
            $addBoolean('is_promoted');
            $addBoolean('salary_negotiable');

            if (!Schema::hasColumn('jobs', 'status')) {
                $table->string('status', 50)->default('active');
            }

            if (!Schema::hasColumn('jobs', 'views_count')) {
                $table->integer('views_count')->default(0);
            }

            if (!Schema::hasColumn('jobs', 'views')) {
                $table->integer('views')->default(0);
            }

            if (!Schema::hasColumn('jobs', 'applications_count')) {
                $table->integer('applications_count')->default(0);
            }

            if (!Schema::hasColumn('jobs', 'saves_count')) {
                $table->integer('saves_count')->default(0);
            }

            if (!Schema::hasColumn('jobs', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'posted_at')) {
                $table->timestamp('posted_at')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'featured_until')) {
                $table->timestamp('featured_until')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'sponsored_until')) {
                $table->timestamp('sponsored_until')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'promoted_until')) {
                $table->timestamp('promoted_until')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'last_application_at')) {
                $table->timestamp('last_application_at')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'salary_type')) {
                $table->string('salary_type', 20)->nullable();
            }

            if (!Schema::hasColumn('jobs', 'pricing_plan_id')) {
                $table->unsignedBigInteger('pricing_plan_id')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'promotion_type')) {
                $table->string('promotion_type', 50)->default('basic');
            }

            if (!Schema::hasColumn('jobs', 'promotion_expires_at')) {
                $table->timestamp('promotion_expires_at')->nullable();
            }

            if (!Schema::hasColumn('jobs', 'gallery')) {
                $table->json('gallery')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Non-destructive migration; columns are left in place on rollback.
    }
};
