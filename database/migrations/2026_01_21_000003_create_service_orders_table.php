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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('buyer_id');
            $table->unsignedInteger('seller_id');
            $table->unsignedInteger('package_id')->nullable();
            $table->json('requirements');
            $table->decimal('total_price', 10, 2);
            $table->string('delivery_time');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->text('buyer_notes')->nullable();
            $table->text('seller_notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('buyer_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('seller_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('service_packages')->onDelete('set null');
            
            $table->index(['service_id', 'status']);
            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
