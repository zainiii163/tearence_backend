<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_seeker_id')->nullable()->constrained()->onDelete('set null');
            
            // Applicant Information
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            
            // Application Content
            $table->text('cover_letter')->nullable();
            $table->string('cv_file_url')->nullable();
            $table->json('portfolio_links')->nullable(); // array of URLs
            $table->string('expected_salary')->nullable(); // min-max format
            $table->date('available_start_date')->nullable();
            $table->text('additional_notes')->nullable();
            
            // Application Status
            $table->string('status')->default('submitted'); // submitted, viewed, shortlisted, interview_scheduled, rejected, hired, withdrawn
            $table->text('employer_notes')->nullable();
            $table->text('next_steps')->nullable();
            $table->timestamp('status_updated_at')->nullable();
            
            // Interview Details
            $table->timestamp('interview_date')->nullable();
            $table->string('interview_type')->nullable(); // phone, video, in_person
            $table->text('interview_notes')->nullable();
            
            // Analytics
            $table->timestamp('viewed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['job_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->unique(['job_id', 'user_id']); // One application per user per job
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
