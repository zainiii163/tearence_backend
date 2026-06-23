<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE images_adverts MODIFY COLUMN license_type ENUM('royalty_free', 'rights_managed', 'extended', 'editorial', 'exclusive') DEFAULT 'royalty_free'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE images_adverts MODIFY COLUMN license_type ENUM('standard', 'extended', 'editorial', 'exclusive') DEFAULT 'standard'");
    }
};
