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
        Schema::table('customer_business', function (Blueprint $table) {
            $table->string('business_company_no', 50)->nullable()->after('business_owner');
            $table->string('business_company_name', 100)->nullable()->after('business_owner');
            $table->string('business_company_registration', 50)->nullable()->after('business_owner');
            $table->string('personal_email', 50)->nullable()->after('business_owner');
            $table->string('personal_phone_number', 20)->nullable()->after('business_owner');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customer_business', function (Blueprint $table) {
            $table->dropColumn('business_company_no');
            $table->dropColumn('business_company_name');
            $table->dropColumn('business_company_registration');
            $table->dropColumn('personal_email');
            $table->dropColumn('personal_phone_number');
        });
    }
};
