<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id'); // FK constraint removed due to missing customer table
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('tagline')->nullable();
            $table->enum('project_type', ['personal', 'startup', 'community', 'creative']);
            $table->enum('category', [
                'technology', 'creative_arts', 'community_social_impact', 
                'health_wellness', 'education', 'real_estate', 
                'environment', 'startups_business', 'other'
            ]);
            $table->text('description');
            $table->text('problem_solved');
            $table->text('vision_mission');
            $table->text('why_matters_now');
            $table->decimal('funding_goal', 12, 2);
            $table->decimal('minimum_contribution', 12, 2)->default(1);
            $table->enum('funding_model', ['donation', 'reward_based', 'equity', 'loan_based']);
            $table->decimal('current_funded', 12, 2)->default(0);
            $table->integer('backers_count')->default(0);
            $table->date('funding_deadline');
            $table->enum('status', ['draft', 'pending', 'active', 'funded', 'failed', 'cancelled'])->default('draft');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('additional_images')->nullable();
            $table->string('pitch_video_url')->nullable();
            $table->json('team_members')->nullable();
            $table->json('use_of_funds')->nullable();
            $table->json('milestones')->nullable();
            $table->json('social_links')->nullable();
            $table->text('revenue_model')->nullable();
            $table->text('forecasts')->nullable();
            $table->text('risk_disclosures')->nullable();
            $table->string('business_registration_number')->nullable();
            $table->string('website')->nullable();
            $table->json('verification_documents')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'published_at']);
            $table->index(['category', 'status']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['is_promoted', 'published_at']);
            $table->index(['is_sponsored', 'published_at']);
            $table->index('funding_deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_projects');
    }
};
