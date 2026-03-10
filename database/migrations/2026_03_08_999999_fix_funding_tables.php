<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing tables if they exist
        Schema::dropIfExists('funding_upsells');
        Schema::dropIfExists('funding_updates');
        Schema::dropIfExists('funding_backers');
        Schema::dropIfExists('funding_rewards');
        Schema::dropIfExists('funding_projects');

        // Create funding projects table
        Schema::create('funding_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
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

        // Create funding rewards table
        Schema::create('funding_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained('funding_projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('minimum_contribution', 12, 2);
            $table->integer('limit')->nullable();
            $table->integer('claimed_count')->default(0);
            $table->date('estimated_delivery_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['funding_project_id', 'is_active']);
        });

        // Create funding backers table
        Schema::create('funding_backers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained('funding_projects')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
                        $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->foreignId('funding_reward_id')->nullable()->constrained('funding_rewards')->onDelete('set null');
            $table->text('message')->nullable();
            $table->timestamp('backed_at');
            $table->timestamps();
            
            $table->index(['funding_project_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->unique(['funding_project_id', 'customer_id'], 'unique_project_backer');
        });

        // Create funding updates table
        Schema::create('funding_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained('funding_projects')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->json('images')->nullable();
            $table->enum('update_type', ['milestone', 'progress', 'announcement', 'thank_you'])->default('progress');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            
            $table->index(['funding_project_id', 'is_public']);
            $table->index('update_type');
        });

        // Create funding upsells table
        Schema::create('funding_upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained('funding_projects')->onDelete('cascade');
            $table->unsignedBigInteger('customer_id');
                        $table->enum('upsell_type', ['promoted', 'featured', 'sponsored']);
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('GBP');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->integer('duration_days')->default(30);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('payment_reference')->nullable();
            $table->timestamps();
            
            $table->index(['funding_project_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['upsell_type', 'status']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_upsells');
        Schema::dropIfExists('funding_updates');
        Schema::dropIfExists('funding_backers');
        Schema::dropIfExists('funding_rewards');
        Schema::dropIfExists('funding_projects');
    }
};
