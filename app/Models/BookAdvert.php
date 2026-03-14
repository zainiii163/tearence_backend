<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class BookAdvert extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'publication_date' => 'date',
        'promoted_until' => 'datetime',
        'additional_images' => 'array',
        'purchase_links' => 'array',
        'sample_files' => 'array',
        'author_social_links' => 'array',
        'verified_author' => 'boolean',
        'agreed_to_terms' => 'boolean',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'books_adverts';

    /**
     * Get the user that posted the book.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the saves/favorites for the book.
     */
    public function saves(): HasMany
    {
        return $this->hasMany(BookSave::class, 'book_id');
    }

    /**
     * Get the views for the book.
     */
    public function views(): HasMany
    {
        return $this->hasMany(BookView::class, 'book_id');
    }

    /**
     * Get the payments for the book.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BookPayment::class, 'book_id');
    }

    /**
     * Get the analytics for the book.
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AnalyticsReport::class, 'analyzable');
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
        return $query->whereIn('advert_type', ['promoted', 'featured', 'sponsored']);
    }

    /**
     * Scope a query to get featured books.
     */
    public function scopeFeatured($query)
    {
        return $query->whereIn('advert_type', ['featured', 'sponsored']);
    }

    /**
     * Scope a query to get sponsored books.
     */
    public function scopeSponsored($query)
    {
        return $query->where('advert_type', 'sponsored');
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
     * Scope a query to get books by format.
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope a query to get books by book type.
     */
    public function scopeByBookType($query, $bookType)
    {
        return $query->where('book_type', $bookType);
    }

    /**
     * Scope a query to get books by language.
     */
    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope a query to get verified author books.
     */
    public function scopeVerifiedAuthor($query)
    {
        return $query->where('verified_author', true);
    }

    /**
     * Scope a query to get books within price range.
     */
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Scope a query to get books with active promotion.
     */
    public function scopeWithActivePromotion($query)
    {
        return $query->where(function($q) {
            $q->whereNull('promoted_until')
              ->orWhere('promoted_until', '>', now());
        });
    }

    /**
     * Scope a query to search books.
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('title', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('subtitle', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('description', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('author_name', 'LIKE', '%' . $searchTerm . '%')
              ->orWhere('isbn', 'LIKE', '%' . $searchTerm . '%');
        });
    }

    /**
     * Increment the view count.
     */
    public function incrementViews()
    {
        $this->increment('views_count');
        
        // Also record the view in the views table
        $this->views()->create([
            'user_id' => auth('user')->check() ? auth('user')->id() : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Increment the save count.
     */
    public function incrementSaves()
    {
        $this->increment('saves_count');
    }

    /**
     * Decrement the save count.
     */
    public function decrementSaves()
    {
        $this->decrement('saves_count');
    }

    /**
     * Check if the book is saved by the current user.
     */
    public function isSavedByUser($userId = null)
    {
        if (!$userId && auth('user')->check()) {
            $userId = auth('user')->id();
        }
        
        if (!$userId) {
            return false;
        }

        return $this->saves()->where('user_id', $userId)->exists();
    }

    /**
     * Toggle save status for a user.
     */
    public function toggleSave($userId = null)
    {
        if (!$userId && auth('user')->check()) {
            $userId = auth('user')->id();
        }
        
        if (!$userId) {
            return false;
        }

        $existingSave = $this->saves()->where('user_id', $userId)->first();

        if ($existingSave) {
            $existingSave->delete();
            $this->decrementSaves();
            return false; // Unsaved
        } else {
            $this->saves()->create(['user_id' => $userId]);
            $this->incrementSaves();
            return true; // Saved
        }
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
        if ($this->cover_image_url) {
            return $this->cover_image_url;
        }
        return asset('placeholder.png');
    }

    /**
     * Get the author photo URL.
     */
    public function getAuthorPhotoUrlAttribute()
    {
        if ($this->author_photo_url) {
            return $this->author_photo_url;
        }
        return asset('placeholder.png');
    }

    /**
     * Get the book URL (slug-based).
     */
    public function getUrlAttribute()
    {
        return route('books.show', $this->slug);
    }

    /**
     * Get the promotion status.
     */
    public function getPromotionStatusAttribute()
    {
        if ($this->advert_type === 'basic') {
            return 'Basic';
        }

        if ($this->promoted_until && $this->promoted_until->isPast()) {
            return 'Expired';
        }

        return ucfirst($this->advert_type);
    }

    /**
     * Check if the book has active promotion.
     */
    public function hasActivePromotion()
    {
        if ($this->advert_type === 'basic') {
            return false;
        }

        return $this->promoted_until === null || $this->promoted_until->isFuture();
    }

    /**
     * Get the promotion badge color.
     */
    public function getBadgeColorAttribute()
    {
        return match($this->advert_type) {
            'featured' => 'purple',
            'promoted' => 'blue',
            'sponsored' => 'amber',
            default => 'gray'
        };
    }

    /**
     * Get the promotion icon.
     */
    public function getPromotionIconAttribute()
    {
        return match($this->advert_type) {
            'featured' => 'crown',
            'promoted' => 'zap',
            'sponsored' => 'rocket',
            default => 'star'
        };
    }

    /**
     * Create a unique slug.
     */
    public static function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($book) {
            if (empty($book->slug)) {
                $book->slug = static::createUniqueSlug($book->title);
            }
        });

        static::updating(function ($book) {
            if ($book->isDirty('title') && empty($book->slug)) {
                $book->slug = static::createUniqueSlug($book->title);
            }
        });
    }
}
