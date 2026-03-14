<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('trending')->default(false);
            $table->integer('job_count')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['slug', 'active']);
            $table->index('trending');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_categories');
    }
};
