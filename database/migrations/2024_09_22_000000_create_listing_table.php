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
        Schema::create('listing', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('listing_id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('currency_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('type', 50)->default('international');
            $table->string('status', 50)->default('active');
            $table->date('promo_expire_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing');
    }
};
