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
        Schema::create('book_saves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('book_id');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate saves
            $table->unique(['user_id', 'book_id'], 'unique_user_book_save');
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['book_id']);
            $table->index(['created_at']);
            
            // Foreign keys will be added in a separate migration to avoid circular dependencies
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('book_saves');
    }
};
