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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('category', ['concert', 'workshop', 'party', 'festival', 'conference', 'sports', 'cultural', 'food_drink', 'charity', 'other']);
            $table->dateTime('date_time');
            $table->string('country');
            $table->string('city');
            $table->string('venue_name')->nullable();
            $table->decimal('ticket_price', 10, 2)->nullable();
            $table->enum('price_type', ['free', 'paid', 'donation'])->default('free');
            $table->text('description');
            $table->text('schedule')->nullable();
            $table->string('age_restrictions')->nullable();
            $table->string('dress_code')->nullable();
            $table->integer('expected_attendance')->nullable();
            $table->string('ticket_link')->nullable();
            $table->string('contact_email');
            $table->json('social_links')->nullable();
            $table->json('images')->nullable();
            $table->string('video_link')->nullable();
            $table->enum('promotion_tier', ['standard', 'promoted', 'featured', 'sponsored', 'spotlight'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index(['date_time', 'is_active']);
            $table->index(['country', 'city', 'is_active']);
            $table->index(['promotion_tier', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
