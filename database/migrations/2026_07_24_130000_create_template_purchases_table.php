<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('template_purchases')) {
            Schema::create('template_purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('business_template_id')->nullable()->index();
                $table->string('template_slug')->nullable()->index();
                $table->string('title')->nullable();
                $table->string('file_url')->nullable();
                $table->decimal('price_paid', 12, 2)->default(0);
                $table->decimal('fee_percent', 5, 2)->nullable();
                $table->decimal('platform_fee', 12, 2)->nullable();
                $table->decimal('seller_amount', 12, 2)->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_status')->default('pending')->index();
                $table->string('download_token', 64)->nullable()->unique();
                $table->timestamp('download_token_expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_purchases');
    }
};
