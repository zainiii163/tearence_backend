<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clive: HR (employees, leave) + Payroll (salary, location, position, hours).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hr_employees')) {
            Schema::create('hr_employees', function (Blueprint $table) {
                $table->id();
                $table->string('employee_code', 32)->nullable()->unique();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email')->nullable();
                $table->string('phone', 64)->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country', 100)->nullable();
                $table->string('postal_code', 32)->nullable();
                $table->string('job_position')->nullable();
                $table->string('work_location')->nullable();
                $table->decimal('weekly_hours', 5, 2)->nullable();
                $table->date('start_date')->nullable();
                $table->string('status', 32)->default('active'); // active, on_leave, terminated
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hr_leave_requests')) {
            Schema::create('hr_leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hr_employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->string('leave_type', 32); // holiday, sick, unpaid, other
                $table->date('start_date');
                $table->date('end_date');
                $table->unsignedSmallInteger('days')->nullable();
                $table->string('status', 32)->default('pending'); // pending, approved, rejected, cancelled
                $table->text('reason')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hr_payroll_records')) {
            Schema::create('hr_payroll_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hr_employee_id')->constrained('hr_employees')->cascadeOnDelete();
                $table->string('pay_period', 32); // e.g. 2026-08
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                $table->string('job_position')->nullable();
                $table->string('work_location')->nullable();
                $table->decimal('hours_worked', 8, 2)->nullable();
                $table->decimal('hourly_rate', 12, 2)->nullable();
                $table->decimal('salary_amount', 12, 2)->default(0);
                $table->string('currency', 8)->default('USD');
                $table->string('payment_status', 32)->default('draft'); // draft, approved, paid
                $table->date('paid_on')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->unique(['hr_employee_id', 'pay_period']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_payroll_records');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_employees');
    }
};
