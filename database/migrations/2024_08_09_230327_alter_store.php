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
        Schema::table('customer_store', function (Blueprint $table) {
            $table->string('store_logo')->after('status')->nullable();
            $table->string('store_banner')->after('status')->nullable();
            $table->string('store_address')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_store', function (Blueprint $table) {
            $table->dropColumn('store_logo');
            $table->dropColumn('store_banner');
            $table->dropColumn('store_address');
        });
    }
};
