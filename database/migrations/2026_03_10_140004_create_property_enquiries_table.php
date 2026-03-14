<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->enum('type', ['general', 'schedule_viewing', 'price_inquiry', 'financing']);
            $table->boolean('contacted')->default(false);
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();
            
            $table->index(['property_id', 'contacted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_enquiries');
    }
};
