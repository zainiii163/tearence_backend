<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Book extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'publication_date' => 'date',
        'expires_at' => 'datetime',
        'additional_images' => 'array',
        'purchase_links' => 'array',
        'sample_files' => 'array',
        'verified_author' => 'boolean',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'books';

    /**
     * Get the user that posted the book.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the author of the book.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * Get the analytics for the book.
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AnalyticsReport::class, 'analyzable');
    }

    /**
     * Get the upsells for the book.
     */
    public function upsells(): HasMany
    {
        return $this->hasMany(BookUpsell::class);
    }

    /**
     * Get the purchases for the book.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(BookPurchase::class);
    }

    /**
     * Get the saves/favorites for the book.
     */
    public function saves(): HasMany
    {
        return $this->hasMany(BookSave::class);
    }

    /**
     * Scope a query to only include active books.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to get promoted books.
     */
    public function scopePromoted($query)
    {
        return $query->whereIn('advert_type', ['promoted', 'featured', 'sponsored', 'top_category']);
    }

    /**
     * Scope a query to get books by genre.
     */
    public function scopeByGenre($query, $genre)
    {
        return $query->where('genre', $genre);
    }

    /**
     * Scope a query to get books by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Increment the view count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    /**
     * Increment the save count.
     */
    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Get the cover image URL.
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }
        return asset('placeholder.png');
    }
}
