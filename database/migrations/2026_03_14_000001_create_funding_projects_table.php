<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('funding_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->enum('project_type', ['personal', 'startup', 'community', 'creative']);
            $table->enum('category', [
                'technology',
                'creative_arts',
                'community_social_impact',
                'health_wellness',
                'education',
                'real_estate',
                'environment',
                'startups_business',
                'other'
            ]);
            $table->text('description');
            $table->text('problem_solving')->nullable();
            $table->text('vision_mission')->nullable();
            $table->text('why_now')->nullable();
            $table->string('cover_image');
            $table->json('additional_images')->nullable();
            $table->string('country');
            $table->string('city')->nullable();

            // Funding Details
            $table->decimal('funding_goal', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->decimal('minimum_contribution', 10, 2)->nullable();
            $table->enum('funding_model', ['donation', 'reward', 'equity', 'loan', 'hybrid'])->default('reward');
            $table->json('use_of_funds')->nullable(); // Array of {item: string, amount: decimal}
            $table->json('milestones')->nullable(); // Array of {milestone: string, date: date}

            // Team
            $table->json('team_members')->nullable(); // Array of {name, role, photo}

            // Verification
            $table->string('identity_verification')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('business_registration_document')->nullable();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable();

            // Media
            $table->string('pitch_video')->nullable();
            $table->json('documents')->nullable(); // Array of document files

            // Status & Visibility
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_promoted')->default(false);

            // Analytics
            $table->decimal('amount_raised', 15, 2)->default(0);
            $table->integer('backer_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('shares_count')->default(0);

            // Dates
            $table->timestamp('funding_starts_at')->nullable();
            $table->timestamp('funding_ends_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['user_id']);
            $table->index(['category']);
            $table->index(['funding_model']);
            $table->index(['is_active', 'created_at']);
            $table->index(['is_featured', 'is_sponsored', 'is_promoted']);
            $table->index(['country']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('funding_projects');
    }
};