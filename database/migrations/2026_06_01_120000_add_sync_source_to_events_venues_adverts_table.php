<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events_venues_adverts', function (Blueprint $table) {
            $table->string('sync_source_type', 50)->nullable()->after('user_id');
            $table->unsignedBigInteger('sync_source_id')->nullable()->after('sync_source_type');
            $table->unique(['sync_source_type', 'sync_source_id'], 'events_venues_adverts_sync_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('events_venues_adverts', function (Blueprint $table) {
            $table->dropUnique('events_venues_adverts_sync_source_unique');
            $table->dropColumn(['sync_source_type', 'sync_source_id']);
        });
    }
};
