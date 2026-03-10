<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'advertisement';

    protected $primaryKey = 'advertisement_id';

    protected $fillable = [
        'title',
        'description',
        'url',
        'image',
        'type',
        'pricing_plan_id',
        'price',
        'payment_status',
        'payment_transaction_id',
        'is_active',
        'start_date',
        'end_date',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function pricingPlan()
    {
        return $this->belongsTo(AdPricingPlan::class, 'pricing_plan_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
} 