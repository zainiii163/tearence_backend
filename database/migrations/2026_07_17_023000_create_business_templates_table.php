<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('blurb', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('vertical', 50); // business, services, buy-sell, vehicles, books, businesses-for-sale
            $table->string('category_slug', 100)->default('default');
            $table->string('headline', 255)->nullable();
            $table->string('section_description', 500)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('price_label', 50)->nullable(); // e.g. "From $29"
            $table->string('currency', 3)->default('USD');
            $table->string('template_type', 50)->nullable(); // pitch_deck, grant, business_plan, proposal
            $table->string('preview_image')->nullable();
            $table->string('file_url')->nullable();
            $table->enum('status', ['draft', 'active', 'paused', 'sold'])->default('active');
            $table->boolean('is_catalog')->default(true); // seeded platform packs vs seller listings
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('views')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
            $table->index(['vertical', 'category_slug', 'status']);
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_templates');
    }
};
