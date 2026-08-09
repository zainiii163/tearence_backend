<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('image_advert_purchases')) {
            return;
        }

        Schema::create('image_advert_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('image_id')->index();
            $table->string('license_type')->default('royalty_free');
            $table->decimal('price_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('download_token', 64)->nullable()->unique();
            $table->timestamp('download_token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_advert_purchases');
    }
};
