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
        Schema::create('candidate_upsells', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('candidate_upsell_id');
            $table->unsignedBigInteger('candidate_profile_id');
            $table->enum('upsell_type', ['featured_profile', 'job_alerts_boost']); // featured_profile or job_alerts_boost
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->default(30); // Duration in days
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
            $table->string('payment_transaction_id', 255)->nullable();
            $table->enum('payment_status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->text('payment_details')->nullable(); // JSON field for payment details
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('candidate_profile_id')
                ->references('candidate_profile_id')->on('candidate_profiles')
                ->onDelete('cascade');

            // Indexes
            $table->index('candidate_profile_id');
            $table->index('upsell_type');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_upsells');
    }
};

