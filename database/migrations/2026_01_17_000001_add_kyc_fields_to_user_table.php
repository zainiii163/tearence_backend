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
            $table->string('kyc_status', 50)->default('pending')->after('avatar');
            $table->text('kyc_documents')->nullable()->after('kyc_status');
            $table->timestamp('kyc_verified_at')->nullable()->after('kyc_documents');
            $table->text('kyc_rejection_reason')->nullable()->after('kyc_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn([
                'kyc_status',
                'kyc_documents', 
                'kyc_verified_at',
                'kyc_rejection_reason'
            ]);
        });
    }
};
