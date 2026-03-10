<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'target_country',
        'target_category',
        'target_device',
    ];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
}
