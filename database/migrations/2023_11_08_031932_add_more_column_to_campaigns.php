<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreColumnToCampaigns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->bigInteger('views')->default(0)->after('donors');
            $table->dateTime('last_donation')->nullable()->after('views');
            $table->string('location', 100)->nullable()->after('last_donation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('views');
            $table->dropColumn('last_donation');
            $table->dropColumn('location');
        });
    }
}