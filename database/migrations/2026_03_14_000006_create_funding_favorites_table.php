<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('funding_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreignId('funding_project_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'funding_project_id']);
            $table->index(['user_id']);
            $table->index(['funding_project_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('funding_favorites');
    }
};
