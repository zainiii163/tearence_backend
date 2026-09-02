<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Friendships reference users.user_id (Social Hub actor) and support a
     * request / accept / decline flow with optional blocking.
     */
    public function up()
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('addressee_id');
            $table->string('status', 20)->default('pending'); // pending|accepted|declined|blocked|cancelled
            $table->timestamp('responded_at')->nullable();

            $table->unique(['requester_id', 'addressee_id']);
            // Prevent duplicate reciprocal friendships at any status
            $table->index(['addressee_id', 'requester_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('friendships');
    }
};
