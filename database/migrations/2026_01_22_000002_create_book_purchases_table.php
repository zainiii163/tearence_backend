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
        Schema::create('book_purchases', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('purchase_id');
            $table->unsignedInteger('listing_id');
            $table->unsignedInteger('customer_id');
            $table->decimal('price_paid', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending')->comment('pending, completed, failed, refunded');
            $table->string('download_token')->unique()->nullable();
            $table->timestamp('download_token_expires_at')->nullable();
            $table->integer('download_attempts')->default(0);
            $table->timestamp('first_downloaded_at')->nullable();
            $table->timestamp('last_downloaded_at')->nullable();
            $table->integer('total_downloads')->default(0);
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('listing_id')->references('listing_id')->on('listing')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            
            // Indexes
            $table->index(['listing_id', 'customer_id']);
            $table->index('payment_status');
            $table->index('download_token');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_purchases');
    }
};
