<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign key first, then alter the column, then re-add
        Schema::table('featured_adverts', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
        });

        Schema::table('featured_adverts', function (Blueprint $table) {
            $table->unsignedInteger('listing_id')->nullable()->change();
        });

        Schema::table('featured_adverts', function (Blueprint $table) {
            $table->foreign('listing_id')->references('listing_id')->on('listing')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('featured_adverts', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
        });

        Schema::table('featured_adverts', function (Blueprint $table) {
            $table->unsignedInteger('listing_id')->nullable(false)->change();
        });

        Schema::table('featured_adverts', function (Blueprint $table) {
            $table->foreign('listing_id')->references('listing_id')->on('listing')->onDelete('cascade');
        });
    }
};
