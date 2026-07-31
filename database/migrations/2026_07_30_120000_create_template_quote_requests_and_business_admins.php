<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_quote_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('template_title')->nullable();
            $table->string('template_slug')->nullable();
            $table->unsignedBigInteger('template_id')->nullable()->index();
            $table->string('file_url')->nullable();
            $table->string('vertical', 64)->nullable()->index();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('message');
            $table->string('status', 32)->default('new')->index(); // new, contacted, quoted, closed
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable()->index();
            $table->timestamps();
        });

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'is_business_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_business_admin')->default(false);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('template_quote_requests');
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'is_business_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_business_admin');
            });
        }
    }
};
