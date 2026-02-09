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
        Schema::create('affiliate_conversions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('affiliate_program_id');
            $table->unsignedInteger('click_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            $table->enum('conversion_type', ['sale', 'lead', 'signup', 'download', 'other']);
            $table->string('product_name');
            $table->string('customer_email')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('converted_at');
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('affiliate_program_id')->references('id')->on('affiliate_programs')->onDelete('cascade');
            $table->foreign('click_id')->references('id')->on('affiliate_clicks')->onDelete('cascade');
            
            $table->index(['affiliate_program_id', 'status']);
            $table->index(['click_id']);
            $table->index(['status', 'converted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_conversions');
    }
};
