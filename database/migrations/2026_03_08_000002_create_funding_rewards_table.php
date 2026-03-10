<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('minimum_contribution', 12, 2);
            $table->integer('limit')->nullable();
            $table->integer('claimed_count')->default(0);
            $table->date('estimated_delivery_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['funding_project_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_rewards');
    }
};
