<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->longText('story')->nullable();
            $table->string('category');
            $table->string('organizer_name');
            $table->string('organizer_email');
            $table->string('organizer_phone')->nullable();
            $table->decimal('goal_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('deadline')->nullable();
            $table->string('country');
            $table->string('city')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->json('beneficiaries')->nullable();
            $table->text('use_of_funds')->nullable();
            $table->json('milestones')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_urgent')->default(false);
            $table->integer('donor_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->string('status')->default('active');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['category']);
            $table->index(['is_active', 'published_at']);
            $table->index(['is_featured', 'is_urgent']);
            $table->index(['country']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('donations');
    }
};
