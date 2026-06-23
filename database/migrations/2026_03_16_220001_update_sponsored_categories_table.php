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
        Schema::table('sponsored_categories', function (Blueprint $table) {
            // Add missing fields from specification
            $table->integer('count')->default(0)->after('description');
            $table->boolean('active')->default(true)->after('count');
            
            // Add indexes
            $table->index(['active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsored_categories', function (Blueprint $table) {
            $table->dropColumn(['count', 'active']);
        });
    }
};
