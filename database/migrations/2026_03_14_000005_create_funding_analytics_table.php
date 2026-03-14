<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('funding_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funding_project_id')->constrained()->onDelete('cascade');
            $table->integer('views_today')->default(0);
            $table->integer('shares_today')->default(0);
            $table->integer('pledges_today')->default(0);
            $table->decimal('revenue_today', 15, 2)->default(0);
            $table->integer('views_total')->default(0);
            $table->integer('shares_total')->default(0);
            $table->date('tracked_date');
            $table->timestamps();

            // Indexes
            $table->index(['funding_project_id']);
            $table->index(['tracked_date']);
            $table->unique(['funding_project_id', 'tracked_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('funding_analytics');
    }
};
