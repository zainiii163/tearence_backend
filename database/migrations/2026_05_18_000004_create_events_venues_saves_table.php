<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events_venues_saves', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('advert_id');
            $table->foreign('advert_id')->references('id')->on('events_venues_adverts')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['user_id', 'advert_id']);
            $table->index('user_id');
            $table->index('advert_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events_venues_saves');
    }
};
