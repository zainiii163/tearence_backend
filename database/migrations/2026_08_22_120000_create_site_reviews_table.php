<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('site_reviews')) {
            return;
        }

        Schema::create('site_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type', 64)->index();
            $table->string('reviewable_id', 64)->index();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('author_name', 120)->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->string('status', 32)->default('approved')->index();
            $table->timestamps();

            $table->unique(
                ['reviewable_type', 'reviewable_id', 'customer_id'],
                'site_reviews_unique_author'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_reviews');
    }
};
