<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // basic, plus, business
            $table->string('name'); // Basic, Plus, Business
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->unsignedSmallInteger('duration_days')->default(30);
            $table->unsignedSmallInteger('paid_posts')->default(0);
            $table->unsignedSmallInteger('promoted_posts')->default(0);
            $table->unsignedSmallInteger('featured_posts')->default(0);
            $table->unsignedSmallInteger('sponsored_posts')->default(0);
            $table->boolean('support_included')->default(false);
            $table->boolean('marketing_toolkit')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
