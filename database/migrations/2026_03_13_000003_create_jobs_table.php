<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('job_categories')->onDelete('set null');
            $table->foreignId('pricing_plan_id')->nullable()->constrained('job_pricing_plans')->onDelete('set null');
            
            // Job Details
            $table->string('title');
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->text('benefits')->nullable();
            $table->string('skills_needed')->nullable(); // comma-separated skills
            
            // Company Information
            $table->string('company_name');
            $table->text('company_description')->nullable();
            $table->string('company_size')->nullable(); // 1-10, 11-50, 51-200, 201-500, 500+
            $table->string('company_industry')->nullable();
            $table->string('company_founded')->nullable(); // year
            $table->string('logo_url')->nullable();
            $table->string('company_website')->nullable();
            $table->json('company_social')->nullable(); // linkedin, twitter, facebook, etc.
            
            // Location
            $table->string('country');
            $table->string('city');
            $table->string('state')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Job Specifications
            $table->string('work_type'); // Full-time, Part-time, Contract, Freelance, Internship, Temporary
            $table->string('salary_range')->nullable(); // min-max format
            $table->string('currency', 3)->default('USD');
            $table->string('experience_level'); // entry, mid, senior, executive
            $table->string('education_level')->nullable(); // high_school, associate, bachelor, master, doctorate
            $table->boolean('remote_available')->default(false);
            
            // Application Details
            $table->string('application_method'); // email, website, phone, in_person
            $table->string('application_email')->nullable();
            $table->string('application_phone')->nullable();
            $table->string('application_website')->nullable();
            $table->text('application_instructions')->nullable();
            
            // Status and Verification
            $table->string('status')->default('pending_review'); // pending_review, active, expired, draft, rejected
            $table->boolean('verified_employer')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('accurate_info')->default(false);
            
            // Analytics
            $table->integer('views')->default(0);
            $table->integer('applications_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Promotion
            $table->string('promotion_type')->default('basic'); // basic, promoted, featured, sponsored, network
            $table->timestamp('promotion_expires_at')->nullable();
            
            // Media
            $table->json('gallery')->nullable(); // array of image URLs
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'posted_at']);
            $table->index(['country', 'city']);
            $table->index(['category_id', 'status']);
            $table->index(['work_type', 'remote_available']);
            $table->index(['experience_level', 'education_level']);
            $table->index(['promotion_type', 'promotion_expires_at']);
            $table->index('verified_employer');
            $table->fullText(['title', 'company_name', 'description', 'requirements', 'skills_needed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
