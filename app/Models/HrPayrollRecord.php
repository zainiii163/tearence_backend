<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrPayrollRecord extends Model
{
    protected $table = 'hr_payroll_records';

    protected $fillable = [
        'hr_employee_id',
        'pay_period',
        'period_start',
        'period_end',
        'job_position',
        'work_location',
        'hours_worked',
        'hourly_rate',
        'salary_amount',
        'currency',
        'payment_status',
        'paid_on',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_on' => 'date',
        'hours_worked' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'salary_amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'hr_employee_id');
    }
}
