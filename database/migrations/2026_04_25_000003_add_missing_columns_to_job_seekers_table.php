<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            // Add missing columns
            if (!Schema::hasColumn('job_seekers', 'full_name')) {
                $table->string('full_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('job_seekers', 'profession')) {
                $table->string('profession')->nullable()->after('full_name');
            }
            if (!Schema::hasColumn('job_seekers', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('job_seekers', 'education_details')) {
                $table->text('education_details')->nullable()->after('education_level');
            }
            if (!Schema::hasColumn('job_seekers', 'experience_summary')) {
                $table->text('experience_summary')->nullable()->after('education_details');
            }
            if (!Schema::hasColumn('job_seekers', 'salary_expectation')) {
                $table->string('salary_expectation')->nullable()->after('desired_role');
            }
            if (!Schema::hasColumn('job_seekers', 'work_type_preference')) {
                $table->string('work_type_preference')->nullable()->after('salary_expectation');
            }
            if (!Schema::hasColumn('job_seekers', 'remote_availability')) {
                $table->boolean('remote_availability')->default(false)->after('work_type_preference');
            }
            if (!Schema::hasColumn('job_seekers', 'preferred_locations')) {
                $table->json('preferred_locations')->nullable()->after('remote_availability');
            }
            if (!Schema::hasColumn('job_seekers', 'preferred_industries')) {
                $table->json('preferred_industries')->nullable()->after('preferred_locations');
            }
            if (!Schema::hasColumn('job_seekers', 'linkedin_link')) {
                $table->string('linkedin_link')->nullable()->after('portfolio_link');
            }
            if (!Schema::hasColumn('job_seekers', 'github_link')) {
                $table->string('github_link')->nullable()->after('linkedin_link');
            }
            if (!Schema::hasColumn('job_seekers', 'additional_links')) {
                $table->json('additional_links')->nullable()->after('github_link');
            }
            if (!Schema::hasColumn('job_seekers', 'status')) {
                $table->string('status')->default('active')->after('is_active');
            }
            if (!Schema::hasColumn('job_seekers', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false)->after('status');
            }
            if (!Schema::hasColumn('job_seekers', 'accurate_info')) {
                $table->boolean('accurate_info')->default(false)->after('terms_accepted');
            }
            if (!Schema::hasColumn('job_seekers', 'verified_profile')) {
                $table->boolean('verified_profile')->default(false)->after('accurate_info');
            }
            if (!Schema::hasColumn('job_seekers', 'views')) {
                $table->integer('views')->default(0)->after('saves_count');
            }
            if (!Schema::hasColumn('job_seekers', 'profile_views')) {
                $table->integer('profile_views')->default(0)->after('views');
            }
            if (!Schema::hasColumn('job_seekers', 'contact_count')) {
                $table->integer('contact_count')->default(0)->after('profile_views');
            }
            if (!Schema::hasColumn('job_seekers', 'promotion_type')) {
                $table->string('promotion_type')->default('basic')->after('promoted_until');
            }
            if (!Schema::hasColumn('job_seekers', 'promotion_expires_at')) {
                $table->timestamp('promotion_expires_at')->nullable()->after('promotion_type');
            }
            
            // Modify existing columns if needed
            if (Schema::hasColumn('job_seekers', 'years_of_experience')) {
                $table->string('years_of_experience')->change();
            }
            if (Schema::hasColumn('job_seekers', 'education_level')) {
                $table->string('education_level')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'profession',
                'state',
                'education_details',
                'experience_summary',
                'salary_expectation',
                'work_type_preference',
                'remote_availability',
                'preferred_locations',
                'preferred_industries',
                'linkedin_link',
                'github_link',
                'additional_links',
                'status',
                'terms_accepted',
                'accurate_info',
                'verified_profile',
                'views',
                'profile_views',
                'contact_count',
                'promotion_type',
                'promotion_expires_at',
            ]);
        });
    }
};
