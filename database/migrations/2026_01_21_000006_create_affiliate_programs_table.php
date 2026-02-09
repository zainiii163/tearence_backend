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
        Schema::create('affiliate_programs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('category_id');
            $table->string('title');
            $table->text('description');
            $table->enum('affiliate_type', ['user_link', 'program_join', 'product_promotion']);
            $table->string('program_name');
            $table->decimal('commission_rate', 5, 2)->nullable(); // e.g., 10.50%
            $table->enum('affiliate_network', ['amazon', 'clickbank', 'shareasale', 'commission_junction', 'rakuten', 'independent', 'our_program']);
            $table->string('product_category');
            $table->enum('promotion_method', ['link_sharing', 'review', 'tutorial', 'social_media', 'email_marketing']);
            $table->string('affiliate_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('clicks_count')->default(0);
            $table->integer('conversions_count')->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0);
            $table->boolean('approved')->default(true);
            $table->boolean('featured')->default(false);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('cascade');
            $table->foreign('category_id')->references('category_id')->on('category')->onDelete('cascade');
            
            $table->index(['user_id', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['affiliate_type', 'is_active']);
            $table->index(['affiliate_network', 'is_active']);
            $table->index(['featured', 'is_active']);
            $table->index(['approved', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_programs');
    }
};
