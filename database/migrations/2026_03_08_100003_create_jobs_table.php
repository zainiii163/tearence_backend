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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_category_id');
            $table->string('title', 200);
            $table->string('slug', 250)->unique();
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->text('skills_needed')->nullable();
            $table->text('benefits')->nullable();
            $table->string('company_name', 200);
            $table->string('company_website', 255)->nullable();
            $table->string('company_logo', 255)->nullable();
            $table->string('contact_email', 255);
            $table->string('application_link', 500)->nullable();
            $table->enum('application_method', ['email', 'link', 'platform'])->default('email');
            $table->enum('work_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote']);
            $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior', 'executive']);
            $table->enum('education_level', ['high_school', 'diploma', 'bachelor', 'master', 'phd', 'none'])->nullable();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->enum('salary_type', ['hourly', 'monthly', 'yearly', 'project'])->nullable();
            $table->boolean('salary_negotiable')->default(false);
            $table->string('country', 100);
            $table->string('city', 100);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name', 255)->nullable();
            $table->boolean('is_remote')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_verified_employer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('sponsored_until')->nullable();
            $table->timestamp('promoted_until')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->timestamp('last_application_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['job_category_id', 'is_active']);
            $table->index(['work_type', 'is_active']);
            $table->index(['experience_level', 'is_active']);
            $table->index(['country', 'city', 'is_active']);
            $table->index(['is_remote', 'is_active']);
            $table->index(['is_urgent', 'is_active']);
            $table->index(['is_verified_employer', 'is_active']);
            $table->index(['is_featured', 'featured_until']);
            $table->index(['is_sponsored', 'sponsored_until']);
            $table->index(['is_promoted', 'promoted_until']);
            $table->index(['expires_at']);
            $table->index(['created_at', 'is_active']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
