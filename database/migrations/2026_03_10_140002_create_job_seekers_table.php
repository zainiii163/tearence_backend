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
        Schema::create('job_seekers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('profession');
            $table->string('country');
            $table->string('city')->nullable();
            $table->boolean('remote_availability')->default(false);
            $table->integer('years_of_experience')->nullable();
            $table->text('key_skills')->nullable();
            $table->enum('education_level', ['high_school', 'associate', 'bachelor', 'master', 'phd'])->nullable();
            $table->string('cv_file')->nullable();
            $table->string('desired_role')->nullable();
            $table->string('salary_expectation')->nullable();
            $table->enum('work_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote'])->nullable();
            $table->text('industries_interested')->nullable();
            $table->string('profile_photo')->nullable();
            $table->text('bio')->nullable();
            $table->string('portfolio_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('social_links')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();
            
            $table->index(['is_active', 'created_at']);
            $table->index(['country', 'city']);
            $table->index(['work_type']);
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
        Schema::dropIfExists('job_seekers');
    }
};
