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
        Schema::create('customer_business', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('slug');
            $table->string('business_name');
            $table->string('business_phone_number');
            $table->string('business_address');
            $table->string('business_email');
            $table->string('business_logo')->nullable();
            $table->string('business_website')->nullable();
            $table->string('business_owner')->nullable();
            $table->enum('status', ['inactive', 'active'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_business');
    }
};
