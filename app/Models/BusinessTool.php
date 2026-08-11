<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessTool extends Model
{
    protected $table = 'business_tools';

    protected $fillable = [
        'slug',
        'title',
        'blurb',
        'description',
        'tag',
        'category_slug',
        'price',
        'price_label',
        'currency',
        'icon',
        'file_url',
        'preview_url',
        'status',
        'sort_order',
        'purchases_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'purchases_count' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = ['display_price'];

    public function purchases(): HasMany
    {
        return $this->hasMany(BusinessToolPurchase::class, 'tool_id');
    }

    public function getDisplayPriceAttribute(): string
    {
        if (!empty($this->price_label)) {
            return $this->price_label;
        }

        $symbol = $this->currency === 'GBP' ? '£' : ($this->currency === 'EUR' ? '€' : '$');

        return 'From '.$symbol.number_format((float) $this->price, 0);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
