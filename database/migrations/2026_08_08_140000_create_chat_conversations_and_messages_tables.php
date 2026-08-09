<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('conversations')) {
            Schema::create('conversations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('buyer_id')->index();
                $table->unsignedBigInteger('seller_id')->index();
                $table->string('listing_id', 64)->nullable()->index();
                $table->string('listing_type', 32)->nullable()->index(); // classifieds, buysell, vehicles, property, services, business, jobs, sponsored
                $table->string('listing_key', 120)->default('none')->index(); // listing_type:listing_id or none
                $table->string('listing_title')->nullable();
                $table->string('subject')->nullable();
                $table->string('status', 20)->default('open')->index(); // open, closed
                $table->timestamp('last_message_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['buyer_id', 'seller_id', 'listing_key'], 'conversations_participants_listing_unique');
            });
        }

        if (! Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('conversation_id')->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->text('message');
                $table->string('message_type', 32)->default('text');
                $table->timestamp('read_at')->nullable()->index();
                $table->timestamps();

                $table->foreign('conversation_id')
                    ->references('id')
                    ->on('conversations')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('conversations');
    }
};
