<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('local_area_alerts')) {
            return;
        }

        Schema::create('local_area_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id')->nullable()->index();
            $table->string('type', 32)->index(); // parking | traffic
            $table->string('title', 180);
            $table->text('message')->nullable();
            $table->string('city', 120)->nullable()->index();
            $table->string('country', 120)->nullable()->index();
            $table->string('area', 180)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_area_alerts');
    }
};
