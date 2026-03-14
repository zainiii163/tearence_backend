<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_upsells', function (Blueprint $table) {
            $table->id();
            $table->morphs('upsellable'); // job_listing or job_seeker
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('upsell_type', ['promoted', 'featured', 'sponsored', 'network_wide']);
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('payment_id')->nullable();
            $table->text('payment_details')->nullable();
            $table->timestamps();
            
            $table->index(['upsellable_type', 'upsellable_id', 'status']);
            $table->index(['upsell_type', 'status']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_upsells');
    }
};
