<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pricing_plan_id')->nullable()->constrained('job_pricing_plans')->onDelete('set null');
            
            // Personal Information
            $table->string('full_name');
            $table->string('profession');
            $table->text('bio')->nullable();
            $table->string('profile_photo_url')->nullable();
            
            // Location
            $table->string('country');
            $table->string('city');
            $table->string('state')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Professional Details
            $table->string('years_of_experience'); // 0-1, 1-3, 3-5, 5-10, 10+
            $table->string('key_skills'); // comma-separated skills
            $table->string('education_level')->nullable(); // high_school, associate, bachelor, master, doctorate
            $table->text('education_details')->nullable();
            $table->text('experience_summary')->nullable();
            
            // Job Preferences
            $table->string('desired_role')->nullable();
            $table->string('salary_expectation')->nullable(); // min-max format
            $table->string('work_type_preference')->nullable(); // Full-time, Part-time, Contract, Freelance
            $table->boolean('remote_availability')->default(false);
            $table->json('preferred_locations')->nullable(); // array of locations
            $table->json('preferred_industries')->nullable(); // array of industries
            
            // Portfolio and Links
            $table->string('portfolio_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('github_link')->nullable();
            $table->string('cv_file_url')->nullable();
            $table->json('additional_links')->nullable();
            
            // Status and Verification
            $table->string('status')->default('active'); // active, inactive, hidden
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('accurate_info')->default(false);
            $table->boolean('verified_profile')->default(false);
            
            // Analytics
            $table->integer('views')->default(0);
            $table->integer('contact_count')->default(0);
            $table->integer('profile_views')->default(0);
            
            // Promotion
            $table->string('promotion_type')->default('basic'); // basic, promoted, featured, sponsored, network
            $table->timestamp('promotion_expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['country', 'city']);
            $table->index(['profession', 'years_of_experience']);
            $table->index(['work_type_preference', 'remote_availability']);
            $table->index(['promotion_type', 'promotion_expires_at']);
            $table->fullText(['full_name', 'profession', 'key_skills', 'bio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_seekers');
    }
};
