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
        Schema::table('listing', function (Blueprint $table) {
            // Book-specific fields
            $table->string('book_type')->nullable()->comment('physical, pdf, audiobook');
            $table->string('genre')->nullable()->comment('action, education, drama, thriller, fiction, non-fiction, textbook');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('format')->nullable()->comment('e-book, audiobook, physical');
            $table->string('condition')->nullable()->comment('new, like_new, good, fair');
            $table->string('file_path')->nullable()->comment('Path to PDF or audio file');
            $table->string('file_type')->nullable()->comment('pdf, mp3, etc.');
            $table->integer('file_size')->nullable()->comment('File size in bytes');
            $table->string('preview_url')->nullable()->comment('URL to book preview/sample');
            $table->string('website_url')->nullable()->comment('External website where book is sold');
            $table->boolean('is_downloadable')->default(false)->comment('If file can be downloaded after purchase');
            $table->integer('download_count')->default(0)->comment('Number of times downloaded');
            $table->timestamp('last_downloaded_at')->nullable();
            
            // Indexes for performance
            $table->index(['book_type', 'genre']);
            $table->index('author');
            $table->index('isbn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropIndex(['book_type', 'genre']);
            $table->dropIndex('author');
            $table->dropIndex('isbn');
            
            $table->dropColumn([
                'book_type',
                'genre', 
                'author',
                'isbn',
                'format',
                'condition',
                'file_path',
                'file_type',
                'file_size',
                'preview_url',
                'website_url',
                'is_downloadable',
                'download_count',
                'last_downloaded_at'
            ]);
        });
    }
};
