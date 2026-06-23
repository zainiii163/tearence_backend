<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('funding_projects', function (Blueprint $table) {
            $table->longText('description')->change();
            $table->longText('problem_solving')->nullable()->change();
            $table->longText('vision_mission')->nullable()->change();
            $table->longText('why_now')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('funding_projects', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('problem_solving')->nullable()->change();
            $table->text('vision_mission')->nullable()->change();
            $table->text('why_now')->nullable()->change();
        });
    }
};
