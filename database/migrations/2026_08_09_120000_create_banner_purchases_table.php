<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('banner_purchases')) {
            Schema::create('banner_purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('banner_ad_id')->index();
                $table->string('banner_slug')->nullable()->index();
                $table->string('title')->nullable();
                $table->decimal('price_paid', 12, 2)->default(0);
                $table->string('payment_method')->nullable();
                $table->string('payment_id')->nullable();
                $table->string('payment_status')->default('pending')->index();
                $table->string('download_token', 64)->nullable()->unique();
                $table->timestamp('download_token_expires_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_purchases');
    }
};
