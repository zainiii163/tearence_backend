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
        Schema::table('sponsored_adverts', function (Blueprint $table) {
            // Add missing fields from specification
            $table->string('tagline', 500)->nullable()->after('title');
            $table->enum('advert_type', ['buy', 'sell', 'rent', 'offer', 'wanted'])->after('description');
            $table->enum('sponsored_tier', ['basic', 'plus', 'premium'])->nullable()->after('sponsored');
            $table->json('badges')->nullable()->after('views');
            $table->timestamp('expires_at')->nullable()->after('promotion_expires_at');
            
            // Update status enum to match specification
            $table->enum('status', ['pending_payment', 'active', 'paused', 'expired', 'rejected'])->default('pending_payment')->change();
            
            // Add indexes for performance
            $table->index(['sponsored_tier']);
            $table->index(['expires_at']);
            $table->index(['advert_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsored_adverts', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'advert_type', 'sponsored_tier', 'badges', 'expires_at']);
            $table->enum('status', ['active', 'pending', 'expired', 'paused', 'rejected'])->default('pending')->change();
        });
    }
};
