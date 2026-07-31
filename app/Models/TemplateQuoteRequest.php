<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateQuoteRequest extends Model
{
    protected $fillable = [
        'user_id',
        'template_title',
        'template_slug',
        'template_id',
        'file_url',
        'vertical',
        'name',
        'email',
        'phone',
        'company',
        'message',
        'status',
        'admin_notes',
        'assigned_to',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }
}
