<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'campaign_id',
        'anonymous',
        'amount',
        'fee',
        'message',
        'paid',
        // payment related
        'uuid',
        'ref_id',
        'payment_method',
        'payment_url',
        'payment_json',
        'expired_at',
        'paid_at',
    ];

    protected $casts = [
        'anonymous' => 'boolean',
        'paid' => 'boolean',
        'amount' => 'integer',
        'fee' => 'integer',
        'expired_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function campaign() {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}