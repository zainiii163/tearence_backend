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
        Schema::create('customer', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('customer_id');
            $table->string('customer_uid', 10)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('password_hash');
            $table->string('email_recovery')->nullable();
            $table->string('access_token')->nullable();
            $table->string('affiliate_id')->nullable();
            $table->integer('affiliated_members')->default(0);
            $table->string('ip_address')->nullable();
            $table->text('ip_location')->nullable();
            $table->string('ip_latlng')->nullable();
            $table->integer('currency_id')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
