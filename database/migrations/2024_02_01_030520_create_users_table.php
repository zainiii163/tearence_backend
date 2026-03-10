<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
            
            $table->increments('user_id');
            $table->string('user_uid', 100)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 64);
            $table->string('password_reset_key')->nullable();
            $table->unsignedInteger('group_id')->nullable();
            $table->string('timezone')->nullable();
            $table->string('avatar')->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_categories')->default(false);
            $table->boolean('can_manage_listings')->default(false);
            $table->boolean('can_manage_dashboard')->default(false);
            $table->boolean('can_view_analytics')->default(false);
            $table->json('permissions')->nullable();
            $table->string('kyc_status')->default('pending');
            $table->timestamp('kyc_verified_at')->nullable();
            $table->text('kyc_rejection_reason')->nullable();
            $table->json('kyc_documents')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
