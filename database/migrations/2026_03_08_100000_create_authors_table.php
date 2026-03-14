<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('bio')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->unique();
            $table->string('website')->nullable();
            $table->json('social_links')->nullable(); // Twitter, Instagram, LinkedIn, etc.
            $table->string('country', 2);
            $table->boolean('verified')->default(false);
            $table->unsignedInteger('user_id')->nullable(); // Link to user account if author has one
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->integer('books_count')->default(0);
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('total_reviews')->default(0);
            $table->timestamps();
            
            $table->index(['verified', 'country']);
            $table->index('average_rating');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authors');
    }
};
