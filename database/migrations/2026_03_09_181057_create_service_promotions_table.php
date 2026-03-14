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
        Schema::create('service_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->enum('promotion_type', ['promoted', 'featured', 'sponsored', 'network_boost']);
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->integer('duration_days'); // Promotion duration in days
            $table->datetime('starts_at');
            $table->datetime('expires_at');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->json('benefits')->nullable(); // Store benefits as JSON
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'promotion_type']);
            $table->index(['status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_promotions');
    }
};
