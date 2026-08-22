<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $table = 'login_logs';

    protected $guarded = ['id'];

    protected $casts = [
        'successful' => 'boolean',
        'is_admin_backend' => 'boolean',
        'alerted' => 'boolean',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeAdminBackend($query)
    {
        return $query->where('is_admin_backend', true);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->successful) {
            return match ($this->event) {
                '2fa_success' => '2FA success',
                'logout' => 'Logout',
                default => 'Success',
            };
        }

        return match ($this->event) {
            '2fa_failed' => '2FA failed',
            '2fa_pending' => '2FA pending',
            default => 'Failed',
        };
    }
}
