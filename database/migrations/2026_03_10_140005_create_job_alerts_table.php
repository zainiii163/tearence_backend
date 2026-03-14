<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('keywords')->nullable();
            $table->foreignId('job_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->enum('work_type', ['full_time', 'part_time', 'contract', 'temporary', 'internship', 'remote'])->nullable();
            $table->string('salary_range')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['is_active', 'frequency']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_alerts');
    }
};
