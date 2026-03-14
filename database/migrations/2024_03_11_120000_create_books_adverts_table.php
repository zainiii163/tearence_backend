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
        Schema::create('books_adverts', function (Blueprint $table) {
            $table->id();
            
            // Basic Book Information
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('subtitle', 500)->nullable();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('book_type'); // fiction, non-fiction, children, etc.
            $table->string('genre', 100)->nullable();
            $table->string('author_name', 255);
            $table->text('author_bio')->nullable();
            $table->string('author_photo_url', 500)->nullable();
            $table->json('author_social_links')->nullable();
            
            // Publishing Details
            $table->string('publisher', 255)->nullable();
            $table->date('publication_date')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->integer('pages')->nullable();
            $table->string('language', 10);
            $table->string('format', 50); // paperback, hardcover, ebook, audiobook
            $table->string('age_range', 20)->nullable();
            $table->string('series_name', 255)->nullable();
            $table->string('edition', 100)->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            
            // Media
            $table->string('cover_image_url', 500)->nullable();
            $table->json('additional_images')->nullable();
            $table->string('trailer_video_url', 500)->nullable();
            $table->json('sample_files')->nullable(); // PDF, MP3 samples
            
            // Purchase Links
            $table->json('purchase_links')->nullable(); // Amazon, Kobo, etc.
            
            // Location
            $table->string('country', 100);
            $table->text('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Analytics
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('saves_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            
            // Status and Moderation
            $table->enum('status', ['active', 'inactive', 'pending', 'rejected'])->default('pending');
            $table->enum('advert_type', ['basic', 'promoted', 'featured', 'sponsored'])->default('basic');
            $table->integer('upsell_tier')->default(1); // 1=basic, 2=promoted, 3=featured, 4=sponsored
            $table->timestamp('promoted_until')->nullable();
            
            // Author Verification
            $table->boolean('verified_author')->default(false);
            
            // Terms and Conditions
            $table->boolean('agreed_to_terms')->default(false);
            
            // Foreign Keys
            $table->unsignedBigInteger('user_id');
            
            // Indexes
            $table->index(['user_id']);
            $table->index(['genre']);
            $table->index(['country']);
            $table->index(['format']);
            $table->index(['book_type']);
            $table->index(['language']);
            $table->index(['advert_type']);
            $table->index(['status']);
            $table->index(['price']);
            $table->index(['views_count']);
            $table->index(['saves_count']);
            $table->index(['rating']);
            $table->index(['created_at']);
            $table->index(['promoted_until']);
            $table->index(['verified_author']);
            
            // Full-text search index
            $table->fullText(['title', 'subtitle', 'description', 'author_name'], 'books_search_index');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_adverts');
    }
};
