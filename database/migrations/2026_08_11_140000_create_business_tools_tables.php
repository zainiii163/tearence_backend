<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('business_tools')) {
            Schema::create('business_tools', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('title');
                $table->string('blurb', 500)->nullable();
                $table->text('description')->nullable();
                $table->string('tag', 50)->default('Marketing'); // Marketing, Advertising, Affiliates, Growth, Legal
                $table->string('category_slug', 100)->nullable(); // optional homepage category scope
                $table->decimal('price', 10, 2)->default(0);
                $table->string('price_label', 50)->nullable();
                $table->string('currency', 3)->default('USD');
                $table->string('icon', 50)->nullable();
                $table->string('file_url')->nullable();
                $table->string('preview_url')->nullable();
                $table->enum('status', ['draft', 'active', 'paused'])->default('active');
                $table->unsignedInteger('sort_order')->default(0);
                $table->unsignedInteger('purchases_count')->default(0);
                $table->timestamps();

                $table->index(['status', 'sort_order']);
                $table->index(['tag', 'status']);
            });
        }

        if (!Schema::hasTable('business_tool_purchases')) {
            Schema::create('business_tool_purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tool_id');
                $table->unsignedInteger('customer_id');
                $table->decimal('amount', 10, 2)->default(0);
                $table->string('currency', 3)->default('USD');
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_reference', 100)->nullable();
                $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
                $table->string('download_token', 64)->nullable()->unique();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->foreign('tool_id')->references('id')->on('business_tools')->onDelete('cascade');
                $table->index(['customer_id', 'status']);
            });
        }

        if (!Schema::hasTable('business_staff_invites')) {
            Schema::create('business_staff_invites', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('business_id');
                $table->unsignedInteger('invited_by_customer_id');
                $table->string('email');
                $table->string('role', 30)->default('editor'); // admin|manager|editor|viewer
                $table->string('token', 64)->unique();
                $table->enum('status', ['pending', 'accepted', 'revoked', 'expired'])->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();

                $table->index(['business_id', 'status']);
                $table->index(['email', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('business_tool_purchases');
        Schema::dropIfExists('business_tools');
        Schema::dropIfExists('business_staff_invites');
    }
};
