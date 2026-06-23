<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // First, rename columns to match new schema
            if (Schema::hasColumn('jobs', 'job_category_id') && !Schema::hasColumn('jobs', 'category_id')) {
                $table->renameColumn('job_category_id', 'category_id');
            }
            
            if (Schema::hasColumn('jobs', 'company_logo') && !Schema::hasColumn('jobs', 'logo_url')) {
                $table->renameColumn('company_logo', 'logo_url');
            }
            
            if (Schema::hasColumn('jobs', 'contact_email') && !Schema::hasColumn('jobs', 'application_email')) {
                $table->renameColumn('contact_email', 'application_email');
            }
            
            if (Schema::hasColumn('jobs', 'is_remote') && !Schema::hasColumn('jobs', 'remote_available')) {
                $table->renameColumn('is_remote', 'remote_available');
            }
            
            if (Schema::hasColumn('jobs', 'is_verified_employer') && !Schema::hasColumn('jobs', 'verified_employer')) {
                $table->renameColumn('is_verified_employer', 'verified_employer');
            }
            
            if (Schema::hasColumn('jobs', 'views_count') && !Schema::hasColumn('jobs', 'views')) {
                $table->renameColumn('views_count', 'views');
            }
            
            // Add missing columns from new schema (without after clauses to avoid ordering issues)
            if (!Schema::hasColumn('jobs', 'status')) {
                $table->string('status')->default('pending_review');
            }
            
            if (!Schema::hasColumn('jobs', 'pricing_plan_id')) {
                $table->foreignId('pricing_plan_id')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'company_description')) {
                $table->text('company_description')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'company_size')) {
                $table->string('company_size')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'company_industry')) {
                $table->string('company_industry')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'company_founded')) {
                $table->string('company_founded')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'state')) {
                $table->string('state')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'address')) {
                $table->text('address')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'salary_range')) {
                $table->string('salary_range')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'currency')) {
                $table->string('currency')->default('USD');
            }
            
            if (!Schema::hasColumn('jobs', 'education_level')) {
                $table->string('education_level')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'application_phone')) {
                $table->string('application_phone')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'application_instructions')) {
                $table->text('application_instructions')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false);
            }
            
            if (!Schema::hasColumn('jobs', 'accurate_info')) {
                $table->boolean('accurate_info')->default(false);
            }
            
            if (!Schema::hasColumn('jobs', 'posted_at')) {
                $table->timestamp('posted_at')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'promotion_type')) {
                $table->string('promotion_type')->default('basic');
            }
            
            if (!Schema::hasColumn('jobs', 'promotion_expires_at')) {
                $table->timestamp('promotion_expires_at')->nullable();
            }
            
            if (!Schema::hasColumn('jobs', 'gallery')) {
                $table->json('gallery')->nullable();
            }
            
            // Add foreign key for category_id if it doesn't exist
            if (Schema::hasColumn('jobs', 'category_id')) {
                $table->foreign('category_id')->references('id')->on('job_categories')->onDelete('set null');
            }
            
            // Add foreign key for pricing_plan_id if it doesn't exist
            if (Schema::hasColumn('jobs', 'pricing_plan_id')) {
                $table->foreign('pricing_plan_id')->references('id')->on('job_pricing_plans')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            // Reverse column renames
            if (Schema::hasColumn('jobs', 'category_id') && !Schema::hasColumn('jobs', 'job_category_id')) {
                $table->renameColumn('category_id', 'job_category_id');
            }
            
            if (Schema::hasColumn('jobs', 'logo_url') && !Schema::hasColumn('jobs', 'company_logo')) {
                $table->renameColumn('logo_url', 'company_logo');
            }
            
            if (Schema::hasColumn('jobs', 'application_email') && !Schema::hasColumn('jobs', 'contact_email')) {
                $table->renameColumn('application_email', 'contact_email');
            }
            
            if (Schema::hasColumn('jobs', 'remote_available') && !Schema::hasColumn('jobs', 'is_remote')) {
                $table->renameColumn('remote_available', 'is_remote');
            }
            
            if (Schema::hasColumn('jobs', 'verified_employer') && !Schema::hasColumn('jobs', 'is_verified_employer')) {
                $table->renameColumn('verified_employer', 'is_verified_employer');
            }
            
            if (Schema::hasColumn('jobs', 'views') && !Schema::hasColumn('jobs', 'views_count')) {
                $table->renameColumn('views', 'views_count');
            }
            
            // Drop added columns (check each individually to avoid errors)
            $columnsToDrop = [
                'status',
                'pricing_plan_id',
                'company_description',
                'company_size',
                'company_industry',
                'company_founded',
                'state',
                'address',
                'salary_range',
                'currency',
                'education_level',
                'remote_available',
                'application_phone',
                'application_website',
                'application_instructions',
                'terms_accepted',
                'accurate_info',
                'posted_at',
                'promotion_type',
                'promotion_expires_at',
                'gallery',
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('jobs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
