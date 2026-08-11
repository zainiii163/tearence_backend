<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BusinessStaffInvite extends Model
{
    protected $table = 'business_staff_invites';

    protected $fillable = [
        'business_id',
        'invited_by_customer_id',
        'email',
        'role',
        'token',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(CustomerBusiness::class, 'business_id');
    }

    public static function mintToken(): string
    {
        return Str::random(48);
    }

    public function isPending(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
