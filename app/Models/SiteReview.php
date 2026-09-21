<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteReview extends Model
{
    protected $table = 'site_reviews';

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'customer_id',
        'author_name',
        'rating',
        'comment',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForTarget($query, string $type, string $id)
    {
        return $query->where('reviewable_type', $type)->where('reviewable_id', (string) $id);
    }
}
