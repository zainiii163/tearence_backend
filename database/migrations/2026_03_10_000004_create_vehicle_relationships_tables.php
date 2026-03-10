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
        Schema::create('vehicle_favourites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('vehicle_id');
            $table->timestamps();
            
            $table->unique(['user_id', 'vehicle_id']);
        });

        Schema::create('vehicle_analytics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('event_type'); // view, click, enquiry, save
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
                        $table->json('metadata')->nullable(); // Additional data like location, referrer, etc.
            $table->timestamps();
            
            $table->index(['vehicle_id', 'event_type']);
            $table->index(['created_at']);
        });

        Schema::create('vehicle_enquiries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
                        $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->enum('status', ['pending', 'replied', 'closed'])->default('pending');
            $table->timestamps();
            
            $table->index(['vehicle_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_enquiries');
        Schema::dropIfExists('vehicle_analytics');
        Schema::dropIfExists('vehicle_favourites');
    }
};
