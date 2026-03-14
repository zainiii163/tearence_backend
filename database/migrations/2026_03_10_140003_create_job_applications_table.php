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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('cv_file')->nullable();
            $table->enum('status', ['pending', 'viewed', 'shortlisted', 'rejected', 'hired'])->default('pending');
            $table->text('employer_notes')->nullable();
            $table->timestamp('applied_at');
            $table->timestamps();
            
            $table->index(['job_listing_id', 'status']);
            $table->index(['user_id']);
            $table->index(['status', 'applied_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_applications');
    }
};
