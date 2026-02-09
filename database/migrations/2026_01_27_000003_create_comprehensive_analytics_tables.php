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
        Schema::create('user_analytics', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('analytics_id');
            $table->unsignedInteger('user_id');
            $table->enum('event_type', [
                'login', 'logout', 'profile_view', 'listing_created', 'listing_updated', 
                'listing_deleted', 'search', 'favorite_added', 'favorite_removed', 
                'message_sent', 'message_received', 'kyc_submitted', 'kyc_approved',
                'kyc_rejected', 'payment_made', 'payment_received', 'account_created'
            ])->default('login');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('event_date')->useCurrent();
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')
                ->references('user_id')->on('user')
                ->onDelete('cascade');

            // Indexes
            $table->index('user_id');
            $table->index('event_type');
            $table->index('event_date');
            $table->index(['user_id', 'event_type']);
            $table->index(['user_id', 'event_date']);
        });

        Schema::create('system_analytics', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('analytics_id');
            $table->enum('metric_type', [
                'total_users', 'active_users', 'total_listings', 'active_listings',
                'total_revenue', 'daily_revenue', 'page_views', 'unique_visitors',
                'new_registrations', 'kyc_submissions', 'kyc_approvals', 'kyc_rejections',
                'support_tickets', 'resolved_tickets', 'pending_tickets'
            ])->default('total_users');
            $table->integer('metric_value')->default(0);
            $table->decimal('metric_value_decimal', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('breakdown')->nullable(); // Detailed breakdown of metrics
            $table->date('metric_date');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('metric_type');
            $table->index('metric_date');
            $table->index(['metric_type', 'metric_date']);
        });

        Schema::create('dashboard_permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('permission_id');
            $table->unsignedInteger('group_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('dashboard_section');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_export')->default(false);
            $table->json('filters')->nullable(); // What filters they can access
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Foreign keys
            $table->foreign('group_id')
                ->references('group_id')->on('group')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('user_id')->on('user')
                ->onDelete('cascade');

            // Indexes
            $table->index('group_id');
            $table->index('user_id');
            $table->index('dashboard_section');
            $table->unique(['group_id', 'user_id', 'dashboard_section'], 'dash_perm_unique');
        });

        Schema::create('analytics_reports', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('report_id');
            $table->unsignedInteger('created_by');
            $table->string('report_name');
            $table->text('report_description')->nullable();
            $table->enum('report_type', [
                'user_activity', 'listing_performance', 'revenue_analysis', 
                'system_health', 'custom'
            ])->default('custom');
            $table->json('filters'); // Report filters and parameters
            $table->json('report_data'); // Cached report data
            $table->boolean('is_public')->default(false);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('created_by')
                ->references('user_id')->on('user')
                ->onDelete('cascade');

            // Indexes
            $table->index('created_by');
            $table->index('report_type');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_reports');
        Schema::dropIfExists('dashboard_permissions');
        Schema::dropIfExists('system_analytics');
        Schema::dropIfExists('user_analytics');
    }
};
