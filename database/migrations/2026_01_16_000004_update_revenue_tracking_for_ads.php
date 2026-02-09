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
        Schema::table('revenue_tracking', function (Blueprint $table) {
            // Add ad-specific fields
            $table->string('ad_type', 20)->nullable()->after('revenue_type'); // banner, affiliate
            $table->unsignedBigInteger('banner_id')->nullable()->after('related_id');
            $table->unsignedBigInteger('affiliate_id')->nullable()->after('banner_id');
            $table->text('description')->nullable()->after('notes');
            
            // Add indexes for new fields
            $table->index('ad_type');
            $table->index('banner_id');
            $table->index('affiliate_id');
            
            // Add foreign keys if tables exist
            if (Schema::hasTable('banner')) {
                $table->foreign('banner_id')->references('id')->on('banner')->onDelete('set null');
            }
            if (Schema::hasTable('affiliate_links')) {
                $table->foreign('affiliate_id')->references('id')->on('affiliate_links')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revenue_tracking', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['banner_id']);
            $table->dropForeign(['affiliate_id']);
            
            // Drop indexes
            $table->dropIndex(['ad_type']);
            $table->dropIndex(['banner_id']);
            $table->dropIndex(['affiliate_id']);
            
            // Drop columns
            $table->dropColumn(['ad_type', 'banner_id', 'affiliate_id', 'description']);
            
            // Revert enum to original values (this might need manual adjustment)
            // $table->enum('revenue_type', ['job_upsell', 'candidate_upsell'])->default('job_upsell')->change();
        });
    }
};
