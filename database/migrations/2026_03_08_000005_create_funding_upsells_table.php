<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_upsells', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funding_project_id');
            $table->unsignedBigInteger('customer_id');
            $table->enum('upsell_type', ['promoted', 'featured', 'sponsored']);
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('GBP');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->integer('duration_days')->default(30);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('payment_reference')->nullable();
            $table->timestamps();
            
            $table->index(['funding_project_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['upsell_type', 'status']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_upsells');
    }
};
