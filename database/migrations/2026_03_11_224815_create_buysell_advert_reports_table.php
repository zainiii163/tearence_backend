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
        Schema::create('buysell_advert_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('advert_id');
            $table->integer('reporter_id');
            $table->string('reason', 100);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->integer('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Foreign Keys - Commented out due to constraint issues
            // $table->foreign('advert_id')->references('id')->on('buysell_adverts')->onDelete('cascade');
            // $table->foreign('reporter_id')->references('user_id')->on('users')->onDelete('cascade');
            // $table->foreign('reviewed_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buysell_advert_reports');
    }
};
