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
        Schema::table('category', function (Blueprint $table) {
            // Store category-specific filter configuration as JSON
            // This allows each category to have its own filter schema
            $table->json('filter_config')->nullable()->after('description');
            
            // Additional metadata for category pages
            $table->string('page_title')->nullable()->after('filter_config');
            $table->text('page_meta_description')->nullable()->after('page_title');
            $table->string('icon_color', 50)->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('category', function (Blueprint $table) {
            $table->dropColumn([
                'filter_config',
                'page_title',
                'page_meta_description',
                'icon_color',
            ]);
        });
    }
};

