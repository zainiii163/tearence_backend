<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ClickBank-style promoter hop links on affiliate applications.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliate_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('affiliate_applications', 'tracking_code')) {
                $table->string('tracking_code', 32)->nullable()->unique()->after('status');
            }
            if (!Schema::hasColumn('affiliate_applications', 'clicks_count')) {
                $table->unsignedInteger('clicks_count')->default(0)->after('tracking_code');
            }
            if (!Schema::hasColumn('affiliate_applications', 'conversions_count')) {
                $table->unsignedInteger('conversions_count')->default(0)->after('clicks_count');
            }
            if (!Schema::hasColumn('affiliate_applications', 'earnings_total')) {
                $table->decimal('earnings_total', 12, 2)->default(0)->after('conversions_count');
            }
            if (!Schema::hasColumn('affiliate_applications', 'joined_at')) {
                $table->timestamp('joined_at')->nullable()->after('earnings_total');
            }
        });

        Schema::create('affiliate_hop_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_application_id')->constrained('affiliate_applications')->cascadeOnDelete();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('referer', 512)->nullable();
            $table->timestamps();

            $table->index(['affiliate_application_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_hop_clicks');

        Schema::table('affiliate_applications', function (Blueprint $table) {
            foreach (['tracking_code', 'clicks_count', 'conversions_count', 'earnings_total', 'joined_at'] as $col) {
                if (Schema::hasColumn('affiliate_applications', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
