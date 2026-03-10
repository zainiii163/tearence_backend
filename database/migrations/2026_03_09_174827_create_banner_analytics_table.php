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
        Schema::create('banner_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_ad_id')->constrained('banner_ads')->onDelete('cascade');
            $table->date('date');
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->decimal('ctr', 5, 2)->default(0); // Click-through rate
            $table->string('country')->nullable();
            $table->string('device_type')->nullable(); // desktop, mobile, tablet
            $table->string('referrer')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['banner_ad_id', 'date']);
            $table->index(['date']);
            $table->index(['country']);
            $table->index(['device_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_analytics');
    }
};
