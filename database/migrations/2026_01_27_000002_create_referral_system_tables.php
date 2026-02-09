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
        Schema::create('referrals', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('referral_id');
            $table->unsignedInteger('referrer_id'); // User who sent the invite
            $table->string('referral_code', 20)->unique(); // Unique referral code
            $table->string('referral_link')->unique(); // Unique referral link
            $table->text('message')->nullable(); // Personal message from referrer
            $table->boolean('is_active')->default(true);
            $table->integer('max_uses')->default(50); // Maximum times this code can be used
            $table->integer('current_uses')->default(0); // Current usage count
            $table->timestamp('expires_at')->nullable(); // When the referral expires
            $table->timestamps();
            
            $table->foreign('referrer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->index('referral_code');
            $table->index('referrer_id');
        });

        Schema::create('user_referrals', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('user_referral_id');
            $table->unsignedInteger('referral_id'); // Link to referral
            $table->unsignedInteger('referred_user_id'); // User who was referred
            $table->unsignedInteger('referrer_user_id'); // User who referred
            $table->string('status', 20)->default('pending'); // pending, completed, expired
            $table->timestamp('registered_at')->nullable(); // When referred user registered
            $table->timestamp('completed_at')->nullable(); // When referral was completed (first listing posted)
            $table->decimal('referrer_discount_amount', 10, 2)->default(0); // Discount given to referrer
            $table->decimal('referred_discount_amount', 10, 2)->default(0); // Discount given to referred user
            $table->string('referrer_discount_type', 20)->default('percentage'); // percentage, fixed
            $table->string('referred_discount_type', 20)->default('percentage'); // percentage, fixed
            $table->boolean('referrer_discount_used')->default(false);
            $table->boolean('referred_discount_used')->default(false);
            $table->timestamps();
            
            $table->foreign('referral_id')->references('referral_id')->on('referrals')->onDelete('cascade');
            $table->foreign('referred_user_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->foreign('referrer_user_id')->references('customer_id')->on('customer')->onDelete('cascade');
            $table->index('referral_id');
            $table->index('referred_user_id');
            $table->index('referrer_user_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_referrals');
        Schema::dropIfExists('referrals');
    }
};
