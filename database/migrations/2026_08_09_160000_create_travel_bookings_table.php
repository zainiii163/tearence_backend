<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('travel_bookings')) {
            return;
        }

        Schema::create('travel_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advert_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('guests')->default(1);
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone', 40)->nullable();
            $table->text('special_requests')->nullable();
            $table->decimal('price_per_unit', 12, 2)->nullable();
            $table->decimal('total_price', 12, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_bookings');
    }
};