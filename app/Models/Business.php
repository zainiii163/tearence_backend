<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'contact_person',
        'email',
        'phone',
        'website',
        'logo',
        'description',
        'country',
        'city',
        'verified',
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }
}
