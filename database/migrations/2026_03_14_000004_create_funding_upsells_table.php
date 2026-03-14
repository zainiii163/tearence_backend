<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('funding_upsells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['promoted', 'featured', 'sponsored']); // promoted, featured, sponsored
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'paid', 'expired', 'cancelled'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['funding_project_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('funding_upsells');
    }
};
