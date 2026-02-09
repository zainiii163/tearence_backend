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
        Schema::create('listing_upsells', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('upsell_id');
            $table->unsignedInteger('listing_id');
            $table->unsignedInteger('customer_id');
            $table->string('upsell_type', 50); // 'priority', 'featured', 'sponsored', 'premium'
            $table->decimal('price', 10, 2);
            $table->integer('duration_days');
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');
            $table->string('status', 50)->default('active'); // 'active', 'expired', 'cancelled'
            $table->string('payment_status', 50)->default('pending'); // 'pending', 'paid', 'failed', 'refunded'
            $table->text('payment_details')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('listing_id')->references('listing_id')->on('listing')->onDelete('cascade');
            $table->foreign('customer_id')->references('customer_id')->on('customer')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['upsell_type', 'status', 'expires_at']);
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_upsells');
    }
};
