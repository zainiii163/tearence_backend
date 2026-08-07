<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_affiliate_posts')) {
            return;
        }

        Schema::table('user_affiliate_posts', function (Blueprint $table) {
            if (Schema::hasColumn('user_affiliate_posts', 'image')) {
                $table->string('image')->nullable()->default(null)->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_affiliate_posts')) {
            return;
        }

        Schema::table('user_affiliate_posts', function (Blueprint $table) {
            if (Schema::hasColumn('user_affiliate_posts', 'image')) {
                $table->string('image')->nullable(false)->default('')->change();
            }
        });
    }
};
