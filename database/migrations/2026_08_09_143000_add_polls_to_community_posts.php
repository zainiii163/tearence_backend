<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table) {
            if (!Schema::hasColumn('community_posts', 'is_poll')) {
                $table->boolean('is_poll')->default(false)->after('discussion_type');
            }
            if (!Schema::hasColumn('community_posts', 'poll_options')) {
                $table->json('poll_options')->nullable()->after('is_poll');
            }
            if (!Schema::hasColumn('community_posts', 'poll_ends_at')) {
                $table->timestamp('poll_ends_at')->nullable()->after('poll_options');
            }
            if (!Schema::hasColumn('community_posts', 'poll_votes_count')) {
                $table->unsignedInteger('poll_votes_count')->default(0)->after('poll_ends_at');
            }
        });

        try {
            DB::statement("ALTER TABLE community_posts MODIFY COLUMN discussion_type ENUM('general','question','review','advice','report','poll') NULL");
        } catch (\Throwable $e) {
            // Non-MySQL or already altered — ignore
        }

        if (!Schema::hasTable('community_poll_votes')) {
            Schema::create('community_poll_votes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('post_id');
                $table->unsignedInteger('user_id');
                $table->string('option_id', 64);
                $table->timestamps();

                $table->foreign('post_id')
                    ->references('post_id')
                    ->on('community_posts')
                    ->onDelete('cascade');
                $table->foreign('user_id')
                    ->references('user_id')
                    ->on('users')
                    ->onDelete('cascade');
                $table->unique(['post_id', 'user_id']);
                $table->index(['post_id', 'option_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_poll_votes');

        Schema::table('community_posts', function (Blueprint $table) {
            foreach (['poll_votes_count', 'poll_ends_at', 'poll_options', 'is_poll'] as $col) {
                if (Schema::hasColumn('community_posts', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        try {
            DB::statement("ALTER TABLE community_posts MODIFY COLUMN discussion_type ENUM('general','question','review','advice','report') NULL");
        } catch (\Throwable $e) {
            // ignore
        }
    }
};
