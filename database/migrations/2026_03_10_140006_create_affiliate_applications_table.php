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
        Schema::create('affiliate_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_affiliate_offer_id')->constrained('business_affiliate_offers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Application Details
            $table->text('message')->nullable(); // Promoter's message to business
            $table->json('promotion_methods')->nullable(); // How they plan to promote
            $table->json('audience_details')->nullable(); // Their audience information
            $table->string('website_url')->nullable();
            $table->string('social_media_links')->nullable(); // JSON object with platform links
            $table->integer('estimated_monthly_visitors')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'approved', 'rejected', 'withdrawn'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('approval_notes')->nullable();
            
            // Admin Actions
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            
            // Business Response
            $table->text('business_response')->nullable();
            $table->timestamp('business_responded_at')->nullable();
            
            $table->timestamps();
            
            // Unique constraint to prevent duplicate applications
            $table->unique(['business_affiliate_offer_id', 'user_id'], 'affiliate_applications_unique');
            
            // Indexes
            $table->index(['status']);
            $table->index(['business_affiliate_offer_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_applications');
    }
};
