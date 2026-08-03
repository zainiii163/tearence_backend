<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('images_adverts', function (Blueprint $table) {
            if (!Schema::hasColumn('images_adverts', 'media_type')) {
                $table->string('media_type', 20)->default('image')->after('main_image'); // image|video
            }
            if (!Schema::hasColumn('images_adverts', 'video_url')) {
                $table->string('video_url')->nullable()->after('media_type');
            }
            if (!Schema::hasColumn('images_adverts', 'video_path')) {
                $table->string('video_path')->nullable()->after('video_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('images_adverts', function (Blueprint $table) {
            if (Schema::hasColumn('images_adverts', 'video_path')) {
                $table->dropColumn('video_path');
            }
            if (Schema::hasColumn('images_adverts', 'video_url')) {
                $table->dropColumn('video_url');
            }
            if (Schema::hasColumn('images_adverts', 'media_type')) {
                $table->dropColumn('media_type');
            }
        });
    }
};
