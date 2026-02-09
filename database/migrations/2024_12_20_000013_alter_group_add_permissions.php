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
        // Check if group table exists
        if (!Schema::hasTable('group')) {
            return;
        }

        Schema::table('group', function (Blueprint $table) {
            // Store group-level permissions
            $table->json('permissions')->nullable()->after('description');
            
            // Permission flags for easier querying
            $table->boolean('can_manage_users')->default(false)->after('permissions');
            $table->boolean('can_manage_categories')->default(false)->after('can_manage_users');
            $table->boolean('can_manage_listings')->default(false)->after('can_manage_categories');
            $table->boolean('can_manage_dashboard')->default(false)->after('can_manage_listings');
            $table->boolean('can_view_analytics')->default(false)->after('can_manage_dashboard');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('group')) {
            return;
        }

        Schema::table('group', function (Blueprint $table) {
            $table->dropColumn([
                'permissions',
                'can_manage_users',
                'can_manage_categories',
                'can_manage_listings',
                'can_manage_dashboard',
                'can_view_analytics',
            ]);
        });
    }
};

