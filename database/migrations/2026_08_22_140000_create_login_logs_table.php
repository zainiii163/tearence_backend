<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->string('guard', 40)->index(); // api, admin, admin-web, web
            $table->string('actor_type', 40)->index(); // customer, user, unknown
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('email', 190)->nullable()->index();
            $table->boolean('successful')->default(false)->index();
            $table->string('event', 40)->default('login')->index(); // login, login_failed, 2fa_pending, 2fa_failed, 2fa_success, logout
            $table->string('failure_reason', 120)->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('country', 100)->nullable()->index();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('latitude', 30)->nullable();
            $table->string('longitude', 30)->nullable();
            $table->string('location_label', 255)->nullable();
            $table->boolean('is_admin_backend')->default(false)->index();
            $table->boolean('alerted')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'is_admin_backend']);
            $table->index(['successful', 'created_at']);
        });

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'can_view_security_logs')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('can_view_security_logs')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_logs');

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'can_view_security_logs')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('can_view_security_logs');
            });
        }
    }
};
