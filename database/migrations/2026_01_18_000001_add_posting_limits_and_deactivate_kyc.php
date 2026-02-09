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
            // Posting limit fields
            $table->unsignedInteger('posts_count')->default(0)->after('kyc_rejection_reason');
            $table->unsignedInteger('posts_limit')->default(5)->after('posts_count');
            $table->timestamp('last_post_at')->nullable()->after('posts_limit');
            $table->boolean('email_verified')->default(false)->after('last_post_at');
            $table->boolean('mobile_verified')->default(false)->after('email_verified');
            $table->timestamp('mobile_verified_at')->nullable()->after('mobile_verified');
            $table->string('mobile_number')->nullable()->after('mobile_verified_at');
            
            // KYC deactivation - set default to disabled for new users
            $table->string('kyc_status', 50)->default('disabled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn([
                'posts_count',
                'posts_limit', 
                'last_post_at',
                'email_verified',
                'mobile_verified',
                'mobile_verified_at',
                'mobile_number'
            ]);
            
            // Revert KYC status to pending
            $table->string('kyc_status', 50)->default('pending')->change();
        });
    }
};
