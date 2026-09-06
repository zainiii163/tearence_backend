<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdvertReport extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'advert_id',
        'advert_slug',
        'advert_type',
        'advert_title',
        'advert_code',
        'reporter_id',
        'reporter_email',
        'reason',
        'severity',
        'description',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
