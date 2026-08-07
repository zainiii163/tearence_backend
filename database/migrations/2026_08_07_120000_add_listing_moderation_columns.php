<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add moderation / post-type columns expected by Listing model and seeders.
     */
    public function up(): void
    {
        if (!Schema::hasTable('listing')) {
            return;
        }

        Schema::table('listing', function (Blueprint $table) {
            if (!Schema::hasColumn('listing', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            }
            if (!Schema::hasColumn('listing', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('listing', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('listing', 'post_type')) {
                $table->string('post_type', 50)->default('regular')->after('rejection_reason');
            }
            if (!Schema::hasColumn('listing', 'is_admin_post')) {
                $table->boolean('is_admin_post')->default(false)->after('post_type');
            }
            if (!Schema::hasColumn('listing', 'is_harmful')) {
                $table->boolean('is_harmful')->default(false)->after('is_admin_post');
            }
            if (!Schema::hasColumn('listing', 'moderation_notes')) {
                $table->text('moderation_notes')->nullable()->after('is_harmful');
            }
            if (!Schema::hasColumn('listing', 'last_reposted_at')) {
                $table->timestamp('last_reposted_at')->nullable()->after('moderation_notes');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('listing')) {
            return;
        }

        Schema::table('listing', function (Blueprint $table) {
            $drop = [];
            foreach ([
                'approved_by',
                'approved_at',
                'rejection_reason',
                'post_type',
                'is_admin_post',
                'is_harmful',
                'moderation_notes',
                'last_reposted_at',
            ] as $col) {
                if (Schema::hasColumn('listing', $col)) {
                    $drop[] = $col;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
