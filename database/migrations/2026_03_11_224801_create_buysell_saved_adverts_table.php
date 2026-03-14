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
        Schema::create('buysell_saved_adverts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('user_id');
            $table->uuid('advert_id');
            $table->timestamps();
            
            // Foreign Keys - Commented out due to constraint issues
            // $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            // $table->foreign('advert_id')->references('id')->on('buysell_adverts')->onDelete('cascade');
            $table->unique(['user_id', 'advert_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buysell_saved_adverts');
    }
};
