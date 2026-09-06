<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('advert_reports')) {
            return;
        }

        Schema::create('advert_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('advert_id')->nullable()->index();
            $table->string('advert_slug')->nullable()->index();
            $table->string('advert_type')->nullable()->index();
            $table->string('advert_title')->nullable();
            $table->string('advert_code')->nullable();
            $table->uuid('reporter_id')->nullable()->index();
            $table->string('reporter_email')->nullable();
            $table->string('reason', 100);
            $table->string('severity', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advert_reports');
    }
};
