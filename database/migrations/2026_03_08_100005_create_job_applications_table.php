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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('job_seeker_id')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('cv_file', 255)->nullable();
            $table->string('portfolio_link', 500)->nullable();
            $table->string('contact_email', 255);
            $table->string('contact_phone', 50)->nullable();
            $table->enum('status', ['pending', 'viewed', 'shortlisted', 'rejected', 'hired'])->default('pending');
            $table->text('employer_notes')->nullable();
            $table->timestamp('applied_at');
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            $table->index(['job_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['job_seeker_id', 'status']);
            $table->index(['status', 'applied_at']);
            $table->unique(['job_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
