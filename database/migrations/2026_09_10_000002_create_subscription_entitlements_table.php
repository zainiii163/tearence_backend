<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_entitlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('entitlement_type')->index(); // paid_post, promoted_post, featured_post, sponsored_post
            $table->unsignedSmallInteger('total')->default(0);
            $table->unsignedSmallInteger('used')->default(0);
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->timestamps();

            $table->foreign('subscription_id')->references('id')->on('user_subscriptions')->onDelete('cascade');
            $table->index(['user_id', 'entitlement_type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_entitlements');
    }
};
