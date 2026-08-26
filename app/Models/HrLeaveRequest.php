<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrLeaveRequest extends Model
{
    protected $table = 'hr_leave_requests';

    protected $fillable = [
        'hr_employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'status',
        'reason',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'hr_employee_id');
    }
}
