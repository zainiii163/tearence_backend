<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('store_products')) {
            Schema::create('store_products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->unsignedBigInteger('seller_id')->nullable()->index();
                $table->string('slug')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('price', 12, 2);
                $table->string('currency', 8)->default('USD');
                $table->string('image_url')->nullable();
                $table->string('category')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('stock')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('store_orders')) {
            Schema::create('store_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_product_id')->nullable()->index();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->unsignedBigInteger('buyer_id')->nullable()->index();
                $table->unsignedBigInteger('seller_id')->nullable()->index();
                $table->string('title');
                $table->decimal('amount', 12, 2);
                $table->string('currency', 8)->default('USD');
                $table->decimal('fee_percent', 5, 2)->nullable();
                $table->decimal('platform_fee', 12, 2)->nullable();
                $table->decimal('seller_amount', 12, 2)->nullable();
                $table->string('payment_status', 32)->default('pending')->index();
                $table->string('payment_method', 32)->nullable();
                $table->string('payment_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('store_orders');
        Schema::dropIfExists('store_products');
    }
};
