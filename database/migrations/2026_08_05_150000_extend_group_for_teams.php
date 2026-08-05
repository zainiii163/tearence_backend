<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('group')) {
            return;
        }

        Schema::table('group', function (Blueprint $table) {
            if (!Schema::hasColumn('group', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('group_id');
            }
            if (!Schema::hasColumn('group', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('group', 'type')) {
                $table->string('type', 20)->default('role')->after('slug'); // team | role
            }
        });

        // Unique slug when present
        Schema::table('group', function (Blueprint $table) {
            try {
                $table->unique('slug');
            } catch (\Throwable $e) {
                // index may already exist
            }
            try {
                $table->index('parent_id');
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                $table->index('type');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('group')) {
            return;
        }

        Schema::table('group', function (Blueprint $table) {
            if (Schema::hasColumn('group', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
            if (Schema::hasColumn('group', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('group', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
