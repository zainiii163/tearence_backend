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
        'is_free' => 'boolean',
        'publication_date' => 'date',
        'expires_at' => 'datetime',
        'additional_images' => 'array',
        'purchase_links' => 'array',
        'sample_files' => 'array',
        'verified_author' => 'boolean',
    ];

    public const CONTENT_KINDS = [
        'book' => 'Book',
        'course' => 'Course',
        'guide' => 'Book guide',
        'manual' => 'Manual',
    ];

    protected $appends = ['cover_image_url'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($book) {
            if (empty($book->status)) {
                // User submissions await publication; admins can set active in Filament.
                $book->status = 'pending';
            }
            if (empty($book->content_kind)) {
                $book->content_kind = 'book';
            }
            if (! isset($book->is_free) && isset($book->price)) {
                $book->is_free = ((float) $book->price) <= 0;
            }
        });
    }

    public function scopeCoursesAndGuides($query)
    {
        return $query->whereIn('content_kind', ['course', 'guide', 'manual']);
    }

    public function scopeOfKind($query, $kind)
    {
        if (is_array($kind)) {
            return $query->whereIn('content_kind', $kind);
        }

        return $query->where('content_kind', $kind);
    }

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
        return $this->belongsTo(User::class, 'user_id', 'user_id');
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
        if (!$this->cover_image) {
            return asset('placeholder.png');
        }

        if (str_starts_with($this->cover_image, 'http://') || str_starts_with($this->cover_image, 'https://')) {
            return $this->cover_image;
        }

        return asset('storage/' . ltrim($this->cover_image, '/'));
    }
}
