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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description');
            $table->string('short_description', 500)->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('cover_image')->nullable();
            $table->json('additional_images')->nullable();
            $table->string('book_type'); // fiction, non-fiction, children, etc.
            $table->string('genre', 100)->nullable();
            $table->string('author_name', 255);
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('country', 2);
            $table->string('language', 10);
            $table->enum('format', ['paperback', 'hardcover', 'ebook', 'audiobook']);
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->date('publication_date')->nullable();
            $table->integer('pages')->nullable();
            $table->string('age_range')->nullable();
            $table->string('series_name')->nullable();
            $table->string('edition')->nullable();
            $table->json('purchase_links')->nullable(); // Amazon, Kobo, etc.
            $table->string('trailer_video_url')->nullable();
            $table->json('sample_files')->nullable(); // PDF, MP3 samples
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('saves_count')->default(0);
            $table->enum('status', ['inactive', 'active', 'pending', 'rejected'])->default('pending');
            $table->enum('advert_type', ['standard', 'promoted', 'featured', 'sponsored', 'top_category'])->default('standard');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('user_id');
            $table->boolean('verified_author')->default(false);
            $table->timestamps();
            
            $table->index(['status', 'advert_type']);
            $table->index(['genre', 'country']);
            $table->index(['created_at', 'views_count']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books');
    }
};
