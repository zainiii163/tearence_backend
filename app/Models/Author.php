<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Author extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'social_links' => 'array',
        'verified' => 'boolean',
        'average_rating' => 'decimal:2',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'authors';

    /**
     * Get the books for the author.
     */
    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    /**
     * Get the user account for the author.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the analytics for the author.
     */
    public function analytics(): MorphMany
    {
        return $this->morphMany(AnalyticsReport::class, 'analyzable');
    }

    /**
     * Scope a query to only include verified authors.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope a query to get authors by country.
     */
    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Get the photo URL.
     */
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return asset('placeholder.png');
    }

    /**
     * Increment the books count.
     */
    public function incrementBooksCount()
    {
        $this->increment('books_count');
    }

    /**
     * Update average rating.
     */
    public function updateAverageRating()
    {
        $totalRating = $this->books()->whereNotNull('rating')->sum('rating');
        $ratedBooks = $this->books()->whereNotNull('rating')->count();
        
        if ($ratedBooks > 0) {
            $this->update([
                'average_rating' => $totalRating / $ratedBooks,
                'total_reviews' => $ratedBooks,
            ]);
        }
    }
}
