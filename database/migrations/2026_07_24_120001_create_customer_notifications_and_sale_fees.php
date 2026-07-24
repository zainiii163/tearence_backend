<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_notifications')) {
            Schema::create('customer_notifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->index();
                $table->string('type', 64)->index();
                $table->string('title')->nullable();
                $table->string('message');
                $table->json('data')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('service_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('service_orders', 'fee_percent')) {
                $table->decimal('fee_percent', 5, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('service_orders', 'platform_fee')) {
                $table->decimal('platform_fee', 12, 2)->nullable()->after('fee_percent');
            }
            if (!Schema::hasColumn('service_orders', 'seller_amount')) {
                $table->decimal('seller_amount', 12, 2)->nullable()->after('platform_fee');
            }
        });

        if (Schema::hasTable('book_purchases')) {
            Schema::table('book_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('book_purchases', 'fee_percent')) {
                    $table->decimal('fee_percent', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('book_purchases', 'platform_fee')) {
                    $table->decimal('platform_fee', 12, 2)->nullable();
                }
                if (!Schema::hasColumn('book_purchases', 'seller_amount')) {
                    $table->decimal('seller_amount', 12, 2)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notifications');

        Schema::table('service_orders', function (Blueprint $table) {
            foreach (['fee_percent', 'platform_fee', 'seller_amount'] as $col) {
                if (Schema::hasColumn('service_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasTable('book_purchases')) {
            Schema::table('book_purchases', function (Blueprint $table) {
                foreach (['fee_percent', 'platform_fee', 'seller_amount'] as $col) {
                    if (Schema::hasColumn('book_purchases', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
