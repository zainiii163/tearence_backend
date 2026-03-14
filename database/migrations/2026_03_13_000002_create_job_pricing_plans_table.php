<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_pricing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('period')->default('month'); // month, week, day
            $table->json('features');
            $table->boolean('recommended')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('duration_months')->default(1);
            $table->integer('visibility_multiplier')->default(1); // 1x, 2x, 3x, etc.
            $table->timestamps();
            
            $table->index(['slug', 'active']);
            $table->index('recommended');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_pricing_plans');
    }
};
