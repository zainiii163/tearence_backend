<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'customer_id',
        'title',
        'thumbnail',
        'status',
        'description',
        'target',
        'collected',
        'donors',
        'views',
        'last_donation',
        'location',
        'target_date',
        'slug',
    ];

    protected $casts = [
        'target' => 'integer',
        'collected' => 'integer',
        'donors' => 'integer',
        'target_date' => 'date',
        'last_donation' => 'date',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}