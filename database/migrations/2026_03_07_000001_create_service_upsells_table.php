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
        Schema::create('service_upsells', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('service_id');
            $table->enum('upsell_type', ['promoted', 'featured', 'sponsored', 'network_boost']);
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->default(30);
            $table->dateTime('starts_at');
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('payment_status')->default('pending');
            $table->string('transaction_id')->nullable();
            $table->json('benefits')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['service_id', 'is_active']);
            $table->index(['upsell_type', 'is_active']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_upsells');
    }
};
