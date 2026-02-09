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
        Schema::table('customer', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('password_hash');
            $table->enum('gender', ['M', 'F'])->nullable()->after('phone');
            $table->date('birthday')->nullable()->after('gender');
            $table->unsignedInteger('address_country')->nullable()->after('birthday');
            $table->unsignedInteger('address_city')->nullable()->after('address_country');
            $table->string('address_street', 128)->nullable()->after('address_city');
            $table->string('address_house', 32)->nullable()->after('address_street');
            $table->string('address_flat', 32)->nullable()->after('address_house');

            // Foreign key constraints
            $table->foreign('address_country')->references('country_id')->on('country')->onDelete('set null');
            $table->foreign('address_city')->references('zone_id')->on('zone')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->dropForeign(['address_country']);
            $table->dropForeign(['address_city']);
            $table->dropColumn([
                'phone',
                'gender',
                'birthday',
                'address_country',
                'address_city',
                'address_street',
                'address_house',
                'address_flat',
            ]);
        });
    }
};

