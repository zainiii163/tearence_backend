<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Alert Configuration
            $table->string('name');
            $table->text('keywords')->nullable(); // search keywords
            $table->string('location')->nullable();
            $table->string('category')->nullable();
            $table->string('work_type')->nullable();
            $table->string('salary_range')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('education_level')->nullable();
            $table->boolean('remote_only')->default(false);
            $table->string('frequency')->default('daily'); // daily, weekly, monthly, instant
            
            // Status
            $table->boolean('active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->integer('jobs_sent_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'active']);
            $table->index(['frequency', 'last_sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
    }
};
