<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer')) {
            return;
        }

        Schema::table('customer', function (Blueprint $table) {
            if (! Schema::hasColumn('customer', 'kyc_status')) {
                $table->string('kyc_status', 50)->default('not_verified')->after('email_verified_at');
            }
            if (! Schema::hasColumn('customer', 'kyc_verified_at')) {
                $table->timestamp('kyc_verified_at')->nullable();
            }
            if (! Schema::hasColumn('customer', 'kyc_rejection_reason')) {
                $table->text('kyc_rejection_reason')->nullable();
            }
            if (! Schema::hasColumn('customer', 'kyc_documents')) {
                $table->json('kyc_documents')->nullable();
            }
            if (! Schema::hasColumn('customer', 'posts_count')) {
                $table->unsignedInteger('posts_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customer')) {
            return;
        }

        Schema::table('customer', function (Blueprint $table) {
            foreach (['kyc_status', 'kyc_verified_at', 'kyc_rejection_reason', 'kyc_documents', 'posts_count'] as $col) {
                if (Schema::hasColumn('customer', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
