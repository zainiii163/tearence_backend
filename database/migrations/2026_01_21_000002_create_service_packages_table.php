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
        Schema::create('service_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->string('name');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('delivery_time');
            $table->integer('revisions')->default(0);
            $table->json('features')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->timestamps();
            
            // Foreign key
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            
            $table->index(['service_id', 'is_popular']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
