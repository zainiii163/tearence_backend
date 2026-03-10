<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_backers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funding_project_id');
            $table->unsignedBigInteger('customer_id');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'completed', 'refunded'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->unsignedBigInteger('funding_reward_id')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('backed_at');
            $table->timestamps();
            
            $table->index(['funding_project_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->unique(['funding_project_id', 'customer_id'], 'unique_project_backer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_backers');
    }
};
