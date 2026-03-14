<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->text('skills_needed')->nullable();
            $table->string('company_name');
            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('country');
            $table->string('city')->nullable();
            $table->enum('work_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote']);
            $table->enum('experience_level', ['entry_level', 'mid_level', 'senior_level', 'executive'])->nullable();
            $table->enum('education_level', ['high_school', 'associate', 'bachelor', 'master', 'phd'])->nullable();
            $table->string('salary_range')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->text('benefits')->nullable();
            $table->enum('application_method', ['email', 'website', 'platform'])->default('platform');
            $table->string('application_email')->nullable();
            $table->string('application_url')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_verified_employer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'created_at']);
            $table->index(['country', 'city']);
            $table->index(['work_type']);
            $table->index(['job_category_id']);
            $table->index(['is_featured', 'is_sponsored', 'is_promoted']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_listings');
    }
};
