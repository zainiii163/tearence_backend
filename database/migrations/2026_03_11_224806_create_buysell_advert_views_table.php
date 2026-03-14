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
        Schema::create('buysell_advert_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('advert_id');
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamp('viewed_at')->useCurrent();
            
            // Foreign Keys - Commented out due to constraint issues
            // $table->foreign('advert_id')->references('id')->on('buysell_adverts')->onDelete('cascade');
            // $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['advert_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buysell_advert_views');
    }
};
