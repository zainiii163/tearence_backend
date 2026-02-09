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
        Schema::table('listing', function (Blueprint $table) {
            // Job-specific fields
            $table->string('job_type', 50)->nullable()->after('description'); // e.g., full-time, part-time, contract, freelance
            $table->decimal('salary_min', 10, 2)->nullable()->after('job_type');
            $table->decimal('salary_max', 10, 2)->nullable()->after('salary_min');
            $table->string('apply_url', 255)->nullable()->after('salary_max');
            $table->date('end_date')->nullable()->after('apply_url');
            $table->boolean('is_featured')->default(false)->after('end_date');
            $table->boolean('is_suggested')->default(false)->after('is_featured');
            $table->dateTime('featured_expires_at')->nullable()->after('is_featured');
            $table->dateTime('suggested_expires_at')->nullable()->after('is_suggested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing', function (Blueprint $table) {
            $table->dropColumn([
                'job_type',
                'salary_min',
                'salary_max',
                'apply_url',
                'end_date',
                'is_featured',
                'is_suggested',
                'featured_expires_at',
                'suggested_expires_at',
            ]);
        });
    }
};

