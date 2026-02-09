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
        Schema::create('service_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('seller_id');
            $table->decimal('rating', 3, 1); // 1.0 to 5.0
            $table->text('comment');
            $table->text('response')->nullable(); // Seller response
            $table->integer('helpful_count')->default(0);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('service_orders')->onDelete('cascade');
            $table->foreign('buyer_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('seller_id')->references('user_id')->on('user')->onDelete('cascade');
            
            $table->unique('order_id'); // One review per order
            $table->index(['service_id', 'rating']);
            $table->index(['seller_id', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reviews');
    }
};
