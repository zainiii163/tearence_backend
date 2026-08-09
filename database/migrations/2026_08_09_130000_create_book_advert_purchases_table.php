<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('book_advert_purchases')) {
            return;
        }

        Schema::create('book_advert_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('book_id')->index();
            $table->string('book_slug')->nullable();
            $table->string('title')->nullable();
            $table->string('format')->nullable();
            $table->decimal('price_paid', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('payment_status')->default('pending'); // pending|completed|failed|refunded
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('download_token', 64)->nullable()->unique();
            $table->timestamp('download_token_expires_at')->nullable();
            $table->unsignedInteger('download_attempts')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'book_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_advert_purchases');
    }
};
