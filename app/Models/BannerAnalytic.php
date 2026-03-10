<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'event_type',
        'ip_address',
        'country',
        'device',
    ];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
}
