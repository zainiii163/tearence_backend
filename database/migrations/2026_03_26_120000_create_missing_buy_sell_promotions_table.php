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
        Schema::create('buy_sell_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->enum('promotion_type', ['promoted', 'featured', 'sponsored', 'network_boost']);
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('features')->nullable(); // What this promotion includes
            $table->timestamps();
            
            $table->foreign('item_id')->references('id')->on('buy_sell_items')->onDelete('cascade');
            $table->index(['promotion_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_sell_promotions');
    }
};
