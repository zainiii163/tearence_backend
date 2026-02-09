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
        Schema::table('user', function (Blueprint $table) {
            // Store user permissions as JSON for flexible role management
            $table->json('permissions')->nullable()->after('group_id');
            
            // Additional fields for user designation and control
            $table->boolean('is_super_admin')->default(false)->after('email_verified_at');
            $table->boolean('can_manage_users')->default(false)->after('is_super_admin');
            $table->boolean('can_manage_categories')->default(false)->after('can_manage_users');
            $table->boolean('can_manage_listings')->default(false)->after('can_manage_categories');
            $table->boolean('can_manage_dashboard')->default(false)->after('can_manage_listings');
            $table->boolean('can_view_analytics')->default(false)->after('can_manage_dashboard');
            $table->boolean('is_active')->default(true)->after('can_view_analytics');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn([
                'permissions',
                'is_super_admin',
                'can_manage_users',
                'can_manage_categories',
                'can_manage_listings',
                'can_manage_dashboard',
                'can_view_analytics',
                'is_active',
            ]);
        });
    }
};

