<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('customer_id');
            $table->string('title', 150);
            $table->text('thumbnail');
            $table->enum('status', ['inactive', 'active'])->default('active');
            $table->text('description')->nullable();
            $table->bigInteger('target')->default(0);
            $table->bigInteger('collected')->default(0);
            $table->integer('donors')->default(0);
            $table->date('target_date')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // relationships
            $table->foreign('customer_id')
                ->references('customer_id')->on('customer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}