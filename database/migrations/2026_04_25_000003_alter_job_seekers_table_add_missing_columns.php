<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            // Rename columns to match model expectations
            if (Schema::hasColumn('job_seekers', 'is_remote_available') && !Schema::hasColumn('job_seekers', 'remote_availability')) {
                $table->renameColumn('is_remote_available', 'remote_availability');
            }
            
            if (Schema::hasColumn('job_seekers', 'views_count') && !Schema::hasColumn('job_seekers', 'views')) {
                $table->renameColumn('views_count', 'views');
            }
            
            if (Schema::hasColumn('job_seekers', 'profile_contacts_count') && !Schema::hasColumn('job_seekers', 'contact_count')) {
                $table->renameColumn('profile_contacts_count', 'contact_count');
            }
            
            // Add missing columns
            if (!Schema::hasColumn('job_seekers', 'status')) {
                $table->string('status')->default('active');
            }
            
            if (!Schema::hasColumn('job_seekers', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false);
            }
            
            if (!Schema::hasColumn('job_seekers', 'accurate_info')) {
                $table->boolean('accurate_info')->default(false);
            }
            
            if (!Schema::hasColumn('job_seekers', 'verified_profile')) {
                $table->boolean('verified_profile')->default(false);
            }
            
            if (!Schema::hasColumn('job_seekers', 'promotion_type')) {
                $table->string('promotion_type')->default('basic');
            }
            
            if (!Schema::hasColumn('job_seekers', 'promotion_expires_at')) {
                $table->timestamp('promotion_expires_at')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'profile_views')) {
                $table->integer('profile_views')->default(0);
            }
            
            if (!Schema::hasColumn('job_seekers', 'state')) {
                $table->string('state')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'full_name')) {
                $table->string('full_name')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'profession')) {
                $table->string('profession')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'title')) {
                $table->string('title')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'bio')) {
                $table->text('bio')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'profile_photo')) {
                $table->string('profile_photo')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'cv_file')) {
                $table->string('cv_file')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'portfolio_link')) {
                $table->string('portfolio_link')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'linkedin_link')) {
                $table->string('linkedin_link')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'github_link')) {
                $table->string('github_link')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'website_url')) {
                $table->string('website_url')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'experience_level')) {
                $table->string('experience_level')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'years_of_experience')) {
                $table->integer('years_of_experience')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'education_level')) {
                $table->string('education_level')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'education_details')) {
                $table->text('education_details')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'experience_summary')) {
                $table->text('experience_summary')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'key_skills')) {
                $table->text('key_skills')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'desired_role')) {
                $table->text('desired_role')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'industries_interested')) {
                $table->text('industries_interested')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'preferred_industries')) {
                $table->json('preferred_industries')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'salary_expectation')) {
                $table->string('salary_expectation')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'salary_expectation_min')) {
                $table->decimal('salary_expectation_min', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'salary_expectation_max')) {
                $table->decimal('salary_expectation_max', 10, 2)->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'salary_currency')) {
                $table->string('salary_currency')->default('USD');
            }
            
            if (!Schema::hasColumn('job_seekers', 'work_type_preference')) {
                $table->string('work_type_preference')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'preferred_work_type')) {
                $table->string('preferred_work_type')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'location_name')) {
                $table->string('location_name')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'preferred_locations')) {
                $table->json('preferred_locations')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'additional_links')) {
                $table->json('additional_links')->nullable();
            }
            
            if (!Schema::hasColumn('job_seekers', 'willing_to_relocate')) {
                $table->boolean('willing_to_relocate')->default(false);
            }
            
            if (!Schema::hasColumn('job_seekers', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            
            if (!Schema::hasColumn('job_seekers', 'is_sponsored')) {
                $table->boolean('is_sponsored')->default(false);
            }
            
            if (!Schema::hasColumn('job_seekers', 'is_promoted')) {
                $table->boolean('is_promoted')->default(false);
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
            
            if (!Schema::hasColumn('job_seekers', 'saves_count')) {
                $table->integer('saves_count')->default(0);
            }
            
            if (!Schema::hasColumn('job_seekers', 'last_contact_at')) {
                $table->timestamp('last_contact_at')->nullable();
            }
            
            // Add foreign key for pricing_plan_id if it doesn't exist
            if (Schema::hasColumn('job_seekers', 'pricing_plan_id')) {
                $table->foreign('pricing_plan_id')->references('id')->on('job_pricing_plans')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seekers', function (Blueprint $table) {
            // Reverse column renames
            if (Schema::hasColumn('job_seekers', 'remote_availability') && !Schema::hasColumn('job_seekers', 'is_remote_available')) {
                $table->renameColumn('remote_availability', 'is_remote_available');
            }
            
            if (Schema::hasColumn('job_seekers', 'views') && !Schema::hasColumn('job_seekers', 'views_count')) {
                $table->renameColumn('views', 'views_count');
            }
            
            if (Schema::hasColumn('job_seekers', 'contact_count') && !Schema::hasColumn('job_seekers', 'profile_contacts_count')) {
                $table->renameColumn('contact_count', 'profile_contacts_count');
            }
            
            // Drop added columns
            $table->dropColumn([
                'status',
                'pricing_plan_id',
                'terms_accepted',
                'accurate_info',
                'verified_profile',
                'promotion_type',
                'promotion_expires_at',
                'profile_views',
                'state',
                'full_name',
                'profession',
                'title',
                'bio',
                'profile_photo',
                'cv_file',
                'portfolio_link',
                'linkedin_link',
                'github_link',
                'website_url',
                'experience_level',
                'years_of_experience',
                'education_level',
                'education_details',
                'experience_summary',
                'key_skills',
                'desired_role',
                'industries_interested',
                'preferred_industries',
                'salary_expectation',
                'salary_expectation_min',
                'salary_expectation_max',
                'salary_currency',
                'work_type_preference',
                'preferred_work_type',
                'location_name',
                'preferred_locations',
                'additional_links',
                'willing_to_relocate',
                'is_featured',
                'is_sponsored',
                'is_promoted',
                'featured_until',
                'sponsored_until',
                'promoted_until',
                'saves_count',
                'last_contact_at',
            ]);
        });
    }
};
