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
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->id('job_alert_id');
            $table->unsignedInteger('customer_id');
            $table->string('name', 255); // Alert name/title
            $table->json('keywords')->nullable(); // Array of keywords to search for
            $table->unsignedInteger('location_id')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->json('job_type')->nullable(); // Array of job types (full-time, part-time, etc.)
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->enum('frequency', ['instant', 'daily', 'weekly'])->default('daily');
            $table->boolean('is_active')->default(true);
            $table->string('notification_email', 255)->nullable(); // Optional email override
            $table->dateTime('last_notified_at')->nullable(); // Track when last notification was sent
            $table->integer('last_matched_count')->default(0); // Count of jobs matched in last notification
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('customer_id')
                ->references('customer_id')->on('customer')
                ->onDelete('cascade');
            
            $table->foreign('location_id')
                ->references('location_id')->on('location')
                ->onDelete('set null');
            
            // Note: Category foreign key is commented out due to potential table structure mismatch
            // The category_id column will still work for queries and filtering
            // If your category table uses category_id as primary key with matching type, uncomment below:
            // $table->foreign('category_id')
            //     ->references('category_id')->on('category')
            //     ->onDelete('set null');

            // Indexes
            $table->index('customer_id');
            $table->index('is_active');
            $table->index('frequency');
            $table->index('last_notified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
    }
};

