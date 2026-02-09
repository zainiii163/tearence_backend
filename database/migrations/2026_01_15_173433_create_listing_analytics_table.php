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
        Schema::create('listing_analytics', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('analytics_id');
            $table->unsignedInteger('listing_id');
            $table->unsignedInteger('customer_id')->nullable(); // User who viewed/clicked
            $table->enum('event_type', ['view', 'click', 'favorite', 'share', 'contact', 'application'])->default('view');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->string('source')->nullable(); // Where the traffic came from
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamp('event_date')->useCurrent();
            $table->timestamps();

            // Foreign keys
            $table->foreign('listing_id')
                ->references('listing_id')->on('listing')
                ->onDelete('cascade');
            
            $table->foreign('customer_id')
                ->references('customer_id')->on('customer')
                ->onDelete('set null');

            // Indexes
            $table->index('listing_id');
            $table->index('customer_id');
            $table->index('event_type');
            $table->index('event_date');
            $table->index(['listing_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_analytics');
    }
};
