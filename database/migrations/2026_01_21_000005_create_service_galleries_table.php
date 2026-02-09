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
        Schema::create('service_galleries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->string('image_path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_video')->default(false);
            $table->string('video_url')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            
            $table->index(['service_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_galleries');
    }
};
