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
        Schema::create('sponsored_live_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advert_id')->nullable()->constrained('sponsored_adverts')->onDelete('cascade');
            $table->enum('type', ['new_advert', 'view', 'contact', 'inquiry', 'click']);
            $table->text('message');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('category', 100)->nullable();
            $table->timestamp('timestamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamps();
            
            // Add indexes
            $table->index(['type']);
            $table->index(['timestamp']);
            $table->index(['advert_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sponsored_live_activity');
    }
};
