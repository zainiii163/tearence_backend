<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('donation_contributions')) {
            return;
        }

        Schema::create('donation_contributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donation_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('donor_name')->nullable();
            $table->string('donor_email')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->boolean('is_anonymous')->default(false);
            $table->string('message', 500)->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_contributions');
    }
};
