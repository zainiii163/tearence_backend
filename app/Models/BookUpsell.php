<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookUpsell extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'payment_date' => 'datetime',
        'benefits' => 'array',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'book_upsells';

    /**
     * Get the book for the upsell.
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user who purchased the upsell.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active upsells.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope a query to get expired upsells.
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->where('status', '!=', 'expired');
    }

    /**
     * Scope a query to get upsells by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('upsell_type', $type);
    }

    /**
     * Check if the upsell is currently active.
     */
    public function isActive()
    {
        return $this->status === 'active' 
               && $this->starts_at <= now() 
               && $this->expires_at > now();
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Get the remaining days.
     */
    public function getRemainingDaysAttribute()
    {
        if ($this->expires_at) {
            return max(0, $this->expires_at->diffInDays(now()));
        }
        return 0;
    }

    /**
     * Get default benefits for each upsell type.
     */
    public static function getDefaultBenefits($type)
    {
        $benefits = [
            'promoted' => [
                'Highlighted listing',
                'Appears above standard book ads',
                '2× more visibility',
                'Promoted badge'
            ],
            'featured' => [
                'Top of genre/category pages',
                'Larger book card',
                'Priority in search results',
                'Included in weekly "Featured Books" email',
                'Featured badge'
            ],
            'sponsored' => [
                'Homepage placement',
                'Category top placement',
                'Included in homepage slider',
                'Included in social media promotion',
                'Sponsored badge',
                'Maximum visibility'
            ],
            'top_category' => [
                'Always pinned at the top of chosen genre',
                'Exclusive "Top of Category" badge',
                'Included in genre newsletters',
                'Included in "Top Picks of the Month" section',
                'Priority over all other tiers',
                'Maximum category dominance'
            ]
        ];

        return $benefits[$type] ?? [];
    }
}
