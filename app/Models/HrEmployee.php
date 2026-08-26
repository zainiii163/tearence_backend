<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrEmployee extends Model
{
    protected $table = 'hr_employees';

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'postal_code',
        'job_position',
        'work_location',
        'weekly_hours',
        'start_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'weekly_hours' => 'decimal:2',
    ];

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(HrLeaveRequest::class, 'hr_employee_id');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(HrPayrollRecord::class, 'hr_employee_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '').' '.($this->last_name ?? ''));
    }
}
