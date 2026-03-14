<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('funding_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('minimum_contribution', 10, 2);
            $table->integer('limit')->nullable();
            $table->integer('claimed_count')->default(0);
            $table->date('estimated_delivery_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['funding_project_id']);
            $table->index(['is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('funding_rewards');
    }
};
