<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('funding_projects')) {
            return;
        }

        // Column was renamed to problem_solved in later schema; only change what exists.
        $map = [
            'description' => 'longtext',
            'problem_solving' => 'longtext',
            'problem_solved' => 'longtext',
            'vision_mission' => 'longtext',
            'why_now' => 'longtext',
        ];

        foreach ($map as $col => $type) {
            if (!Schema::hasColumn('funding_projects', $col)) {
                continue;
            }
            DB::statement("ALTER TABLE `funding_projects` MODIFY `$col` $type NULL");
        }
    }

    public function down()
    {
        if (!Schema::hasTable('funding_projects')) {
            return;
        }

        foreach (['description', 'problem_solving', 'problem_solved', 'vision_mission', 'why_now'] as $col) {
            if (!Schema::hasColumn('funding_projects', $col)) {
                continue;
            }
            DB::statement("ALTER TABLE `funding_projects` MODIFY `$col` text NULL");
        }
    }
};
