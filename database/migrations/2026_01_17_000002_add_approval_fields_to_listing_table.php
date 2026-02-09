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
        Schema::table('listing', function (Blueprint $table) {
            $table->string('approval_status', 50)->default('pending')->after('status');
            $table->unsignedInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->boolean('is_admin_post')->default(false)->after('rejection_reason');
            $table->string('post_type', 50)->default('regular')->after('is_admin_post'); // regular, sponsored, promoted, admin
            $table->timestamp('last_reposted_at')->nullable()->after('updated_at');
            $table->text('moderation_notes')->nullable()->after('last_reposted_at');
            $table->boolean('is_harmful')->default(false)->after('moderation_notes');
            
            $table->foreign('approved_by')->references('user_id')->on('user')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status',
                'approved_by',
                'approved_at',
                'rejection_reason',
                'is_admin_post',
                'post_type',
                'last_reposted_at',
                'moderation_notes',
                'is_harmful'
            ]);
        });
    }
};
