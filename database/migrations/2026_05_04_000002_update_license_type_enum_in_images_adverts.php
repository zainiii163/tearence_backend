<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('images_adverts', function (Blueprint $table) {
            $table->enum('license_type', ['royalty_free', 'rights_managed', 'extended', 'editorial', 'exclusive'])->default('royalty_free')->change();
        });
    }

    public function down()
    {
        Schema::table('images_adverts', function (Blueprint $table) {
            $table->enum('license_type', ['standard', 'extended', 'editorial', 'exclusive'])->default('standard')->change();
        });
    }
};
