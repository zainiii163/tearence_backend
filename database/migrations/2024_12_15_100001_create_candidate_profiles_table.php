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
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('candidate_profile_id');
            $table->unsignedInteger('customer_id');
            $table->string('headline', 255)->nullable();
            $table->text('summary')->nullable();
            $table->json('skills')->nullable(); // Array of skills
            $table->string('cv_url', 255)->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->boolean('is_featured')->default(false);
            $table->dateTime('featured_expires_at')->nullable();
            $table->boolean('has_job_alerts_boost')->default(false);
            $table->dateTime('job_alerts_boost_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('customer_id')
                ->references('customer_id')->on('customer')
                ->onDelete('cascade');
            
            $table->foreign('location_id')
                ->references('location_id')->on('location')
                ->onDelete('set null');

            // Indexes
            $table->index('customer_id');
            $table->index('visibility');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_profiles');
    }
};

