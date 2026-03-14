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
        Schema::create('promoted_advert_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoted_advert_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // view, click, save, inquiry, share
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->json('metadata')->nullable(); // additional event data
            $table->timestamps();
            
            // Indexes
            $table->index(['promoted_advert_id', 'event_type']);
            $table->index(['event_type']);
            $table->index(['created_at']);
            $table->index(['country']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoted_advert_analytics');
    }
};
