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
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0.00)->after('link');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('price');
            $table->string('payment_transaction_id')->nullable()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('payment_transaction_id');
            $table->timestamp('expires_at')->nullable()->after('paid_at');
            $table->boolean('is_active')->default(true)->after('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            $table->dropColumn([
                'price',
                'payment_status',
                'payment_transaction_id',
                'paid_at',
                'expires_at',
                'is_active'
            ]);
        });
    }
};
