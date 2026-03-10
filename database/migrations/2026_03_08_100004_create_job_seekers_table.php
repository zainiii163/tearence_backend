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
        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title', 200)->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_photo', 255)->nullable();
            $table->string('cv_file', 255)->nullable();
            $table->string('portfolio_link', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('github_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->enum('experience_level', ['entry', 'junior', 'mid', 'senior', 'executive'])->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->enum('education_level', ['high_school', 'diploma', 'bachelor', 'master', 'phd', 'none'])->nullable();
            $table->text('key_skills')->nullable();
            $table->text('desired_role')->nullable();
            $table->text('industries_interested')->nullable();
            $table->decimal('salary_expectation_min', 12, 2)->nullable();
            $table->decimal('salary_expectation_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->enum('preferred_work_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote', 'any'])->nullable();
            $table->boolean('is_remote_available')->default(true);
            $table->string('country', 100);
            $table->string('city', 100);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name', 255)->nullable();
            $table->boolean('willing_to_relocate')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->timestamp('featured_until')->nullable();
            $table->timestamp('sponsored_until')->nullable();
            $table->timestamp('promoted_until')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('profile_contacts_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['experience_level', 'is_active']);
            $table->index(['education_level', 'is_active']);
            $table->index(['country', 'city', 'is_active']);
            $table->index(['is_remote_available', 'is_active']);
            $table->index(['is_featured', 'featured_until']);
            $table->index(['is_sponsored', 'sponsored_until']);
            $table->index(['is_promoted', 'promoted_until']);
            $table->index(['created_at', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_seekers');
    }
};
