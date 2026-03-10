<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->json('images')->nullable();
            $table->enum('update_type', ['milestone', 'progress', 'announcement', 'thank_you'])->default('progress');
            $table->boolean('is_public')->default(true);
            $table->timestamps();
            
            $table->index(['funding_project_id', 'is_public']);
            $table->index('update_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_updates');
    }
};
