<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_toolkits', function (Blueprint $table) {
            $table->id();
            $table->string('category_slug')->index(); // property, vehicles, jobs, services, events, resorts, books, buy-sell, business, funding, donations, adverts
            $table->string('tool_type')->index(); // template, guide, checklist, calculator, script, strategy, resource
            $table->string('name');
            $table->text('description');
            $table->longText('content')->nullable(); // detailed content/instructions in markdown
            $table->json('items')->nullable(); // structured items: steps, tips, formulas, etc.
            $table->json('meta')->nullable(); // additional metadata (links, file refs, etc.)
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_slug', 'tool_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_toolkits');
    }
};
