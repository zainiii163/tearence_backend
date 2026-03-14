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
        Schema::create('sponsored_advert_ratings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('rating_id');
            $table->unsignedInteger('sponsored_advert_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->string('name', 255);
            $table->string('email', 255);
            $table->integer('rating'); // 1-5 stars
            $table->text('review')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['sponsored_advert_id', 'is_approved'], 'sponsored_advert_approval_idx');
            $table->index(['rating']);
            $table->index(['created_at']);
            
            // Foreign key constraints
            // Unique constraint to prevent multiple ratings from same email for same advert
            $table->unique(['sponsored_advert_id', 'email'], 'unique_advert_email_rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_advert_ratings');
    }
};
