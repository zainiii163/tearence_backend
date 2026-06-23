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
        Schema::table('affiliate_upsell_plans', function (Blueprint $table) {
            // Add missing columns that the model expects
            $table->integer('duration_days')->after('duration_type')->default(1);
            $table->json('features')->after('duration_days')->nullable();
            $table->text('benefits')->after('features')->nullable();
            $table->boolean('above_standard_posts')->after('highlighted_background')->default(false);
            $table->boolean('top_category_placement')->after('above_standard_posts')->default(false);
            $table->boolean('email_blast_inclusion')->after('weekly_email_blast')->default(false);
            $table->string('badge_text')->after('email_blast_inclusion')->nullable();
            $table->string('badge_color')->after('badge_text')->nullable();
            
            // Copy data from duration_value to duration_days if needed
            // This will be handled in a separate step
        });
        
        // Copy existing duration_value to duration_days
        \DB::statement('UPDATE affiliate_upsell_plans SET duration_days = duration_value WHERE duration_value IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_upsell_plans', function (Blueprint $table) {
            $table->dropColumn([
                'duration_days',
                'features',
                'benefits',
                'above_standard_posts',
                'top_category_placement',
                'email_blast_inclusion',
                'badge_text',
                'badge_color'
            ]);
        });
    }
};
