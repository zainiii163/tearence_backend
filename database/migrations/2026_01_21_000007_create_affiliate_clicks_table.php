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
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('affiliate_program_id');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('clicked_at');
            
            // Foreign key
            $table->foreign('affiliate_program_id')->references('id')->on('affiliate_programs')->onDelete('cascade');
            
            $table->index(['affiliate_program_id', 'clicked_at']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
