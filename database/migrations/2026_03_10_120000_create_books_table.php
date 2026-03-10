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
            
            // Basic Book Information
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('subtitle', 500)->nullable();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('book_type'); // fiction, non-fiction, children, etc.
            $table->string('genre', 100)->nullable();
            $table->string('author_name', 255);
            $table->unsignedBigInteger('author_id')->nullable();
            $table->text('author_bio')->nullable();
            $table->string('author_photo')->nullable();
            $table->json('author_social_links')->nullable();
            
            // Pricing and Format
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('format', ['paperback', 'hardcover', 'ebook', 'audiobook']);
            
            // Media
            $table->string('cover_image')->nullable();
            $table->json('additional_images')->nullable();
            $table->string('trailer_video_url')->nullable();
            $table->json('sample_files')->nullable(); // PDF, MP3 samples
            
            // Publication Details
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->date('publication_date')->nullable();
            $table->integer('pages')->nullable();
            $table->string('age_range')->nullable();
            $table->string('series_name')->nullable();
            $table->string('edition')->nullable();
            
            // Location and Language
            $table->string('country', 2);
            $table->string('language', 10);
            
            // Purchase Links
            $table->json('purchase_links')->nullable(); // Amazon, Kobo, etc.
            
            // Analytics
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedBigInteger('saves_count')->default(0);
            
            // Status and Moderation
            $table->enum('status', ['inactive', 'active', 'pending', 'rejected'])->default('pending');
            
            // Premium Upsell Tiers
            $table->enum('advert_type', ['standard', 'promoted', 'featured', 'sponsored', 'top_category'])->default('standard');
            $table->boolean('is_promoted')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_top_category')->default(false);
            
            // Payment Information
            $table->decimal('upsell_price', 10, 2)->default(0.00);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Author Verification
            $table->boolean('verified_author')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Location for events/signings
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address')->nullable();
            
            // Foreign Keys
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->unsignedBigInteger('pricing_plan_id')->nullable();
            
            // Indexes
            $table->index(['status', 'advert_type']);
            $table->index(['genre', 'country']);
            $table->index(['created_at', 'views_count']);
            $table->index(['payment_status']);
            $table->index(['is_promoted', 'is_featured', 'is_sponsored', 'is_top_category']);
            $table->index(['expires_at']);
            
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
        Schema::dropIfExists('books');
    }
};
