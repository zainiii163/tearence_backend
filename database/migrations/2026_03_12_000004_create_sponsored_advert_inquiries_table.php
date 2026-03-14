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
        Schema::create('sponsored_advert_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsored_advert_id')->constrained('sponsored_adverts')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Contact information
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            
            // Message
            $table->text('message');
            $table->enum('inquiry_type', ['general', 'price_negotiation', 'availability', 'technical'])->default('general');
            
            // Status
            $table->enum('status', ['pending', 'responded', 'closed'])->default('pending');
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['sponsored_advert_id', 'status']);
            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_advert_inquiries');
    }
};
